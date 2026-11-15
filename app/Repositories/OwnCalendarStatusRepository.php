<?php
declare(strict_types=1);

namespace Wappointment\Repositories;

use Wappointment\Booking\BookingException;

class OwnCalendarStatusRepository
{
    private \wpdb $db;

    public function __construct(private BookingRepository $bookings)
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function all(int $calendarId, ?int $owner): array
    {
        $calendar = $this->ownedCalendar($calendarId, $owner);
        $calendarTimezone = $this->calendarTimezone($calendar['options']);
        $rows = $this->rows($this->db->prepare(
            "SELECT id, type, start_at, end_at, recur, muted, source, options FROM {$this->statusTable()}
             WHERE staff_id = %d AND type IN (0, 1) AND source IS NULL AND muted = 0
             ORDER BY start_at, id",
            $calendarId
        ));
        $statuses = [];
        foreach ($rows as $row) {
            if ($this->hasConditions($row['options'])) {
                continue;
            }
            $statuses[] = $this->present($row, $calendarTimezone);
        }
        return $statuses;
    }

    public function create(
        int $calendarId,
        ?int $owner,
        int $type,
        int $start,
        int $end,
        int $recur = 0,
        int $interval = 1,
        array $weekdays = [],
        ?int $monthDay = null,
        ?string $until = null
    ): array
    {
        return $this->bookings->exclusive(function () use (
            $calendarId, $owner, $type, $start, $end, $recur, $interval, $weekdays, $monthDay, $until
        ): array {
            $calendar = $this->lockOwnedCalendar($calendarId, $owner);
            $timezone = $this->calendarTimezone($calendar['options']);
            $options = $this->recurrenceOptions($recur, $interval, $weekdays, $monthDay, $until, $timezone, $start);
            $data = [
                'staff_id' => $calendarId,
                'type' => $type,
                'start_at' => gmdate('Y-m-d H:i:s', $start),
                'end_at' => gmdate('Y-m-d H:i:s', $end),
                'recur' => $recur,
                'muted' => 0,
                'source' => null,
                'eventkey' => null,
                'options' => $this->encodeOptions($options),
            ];
            if ($this->db->insert($this->statusTable(), $data) === false) {
                $this->failure();
            }
            return $this->present([
                'id' => (int) $this->db->insert_id, 'type' => $type, 'start_at' => $data['start_at'],
                'end_at' => $data['end_at'], 'recur' => $recur, 'muted' => 0, 'source' => null,
                'options' => $data['options'],
            ], $timezone);
        });
    }

    public function update(
        int $calendarId,
        int $statusId,
        ?int $owner,
        int $type,
        int $start,
        int $end,
        int $recur = 0,
        int $interval = 1,
        array $weekdays = [],
        ?int $monthDay = null,
        ?string $until = null
    ): array
    {
        return $this->bookings->exclusive(function () use (
            $calendarId, $statusId, $owner, $type, $start, $end, $recur, $interval, $weekdays, $monthDay, $until
        ): array {
            $calendar = $this->lockOwnedCalendar($calendarId, $owner);
            $status = $this->lockEditableStatus($calendarId, $statusId);
            $existingOptions = $this->decodeOptions($status['options']);
            $timezone = $recur > 0
                ? (string) ($existingOptions['origin_tz'] ?? $this->calendarTimezone($calendar['options']))
                : $this->calendarTimezone($calendar['options']);
            $options = $this->recurrenceOptions($recur, $interval, $weekdays, $monthDay, $until, $timezone, $start);
            if ($this->db->update($this->statusTable(), [
                'type' => $type,
                'start_at' => gmdate('Y-m-d H:i:s', $start),
                'end_at' => gmdate('Y-m-d H:i:s', $end),
                'recur' => $recur,
                'options' => $this->encodeOptions($options),
            ], ['id' => $statusId, 'staff_id' => $calendarId]) === false) {
                $this->failure();
            }
            return $this->present([
                'id' => (int) $status['id'], 'type' => $type, 'start_at' => gmdate('Y-m-d H:i:s', $start),
                'end_at' => gmdate('Y-m-d H:i:s', $end), 'recur' => $recur, 'muted' => 0, 'source' => null,
                'options' => $this->encodeOptions($options),
            ], $timezone);
        });
    }

    public function delete(int $calendarId, int $statusId, ?int $owner): void
    {
        $this->bookings->exclusive(function () use ($calendarId, $statusId, $owner): void {
            $this->lockOwnedCalendar($calendarId, $owner);
            $this->lockEditableStatus($calendarId, $statusId);
            if ($this->db->delete($this->statusTable(), ['id' => $statusId, 'staff_id' => $calendarId]) === false) {
                $this->failure();
            }
        });
    }

    private function lockEditableStatus(int $calendarId, int $statusId): array
    {
        $rows = $this->rows($this->db->prepare(
            "SELECT id, type, start_at, recur, muted, source, options FROM {$this->statusTable()}
             WHERE id = %d AND staff_id = %d FOR UPDATE",
            $statusId, $calendarId
        ));
        if (!$rows) {
            throw new BookingException(__('Availability exception not found.', 'wappointment'), 404);
        }
        $status = $rows[0];
        if (!in_array((int) $status['type'], [0, 1], true)
            || (int) $status['recur'] < 0 || (int) $status['recur'] > 4
            || (int) $status['muted'] !== 0 || $status['source'] !== null
            || $this->hasConditions($status['options'])
            || !$this->recurrenceFields((int) $status['recur'], $status['options'], 'UTC', $status['start_at'])['editable']) {
            throw new BookingException(__('This availability exception is managed by another feature and cannot be edited here.', 'wappointment'), 409);
        }
        return $status;
    }

    private function ownedCalendar(int $calendarId, ?int $owner): array
    {
        $scope = $owner === null ? '' : $this->db->prepare(' AND wp_uid = %d', $owner);
        $rows = $this->rows($this->db->prepare(
            "SELECT id, options FROM {$this->calendarTable()} WHERE id = %d{$scope} AND deleted_at IS NULL",
            $calendarId
        ));
        if (!$rows) {
            throw new BookingException(__('Calendar not found.', 'wappointment'), 404);
        }
        return $rows[0];
    }

    private function lockOwnedCalendar(int $calendarId, ?int $owner): array
    {
        $scope = $owner === null ? '' : $this->db->prepare(' AND wp_uid = %d', $owner);
        $rows = $this->rows($this->db->prepare(
            "SELECT id, options FROM {$this->calendarTable()} WHERE id = %d{$scope} AND deleted_at IS NULL FOR UPDATE",
            $calendarId
        ));
        if (!$rows) {
            throw new BookingException(__('Calendar not found.', 'wappointment'), 404);
        }
        return $rows[0];
    }

    private function present(array $row, string $calendarTimezone): array
    {
        $recurrence = $this->recurrenceFields(
            (int) $row['recur'],
            $row['options'],
            $calendarTimezone,
            $row['start_at'] ?? null
        );
        return [
            'id' => (int) $row['id'],
            'type' => (int) $row['type'],
            'start' => $this->bookings->timestamp($row['start_at']),
            'end' => $this->bookings->timestamp($row['end_at']),
            ...$recurrence,
        ];
    }

    private function recurrenceFields(int $recur, ?string $encoded, string $calendarTimezone, ?string $startAt = null): array
    {
        $options = $this->decodeOptions($encoded);
        $timezone = $calendarTimezone;
        $editable = $recur === 0;
        $interval = 1;
        $weekdays = [];
        $monthDay = null;
        $until = null;
        if ($recur > 0) {
            $allowed = ['interval', 'byday', 'bymonthday', 'origin_tz', 'origin_start', 'until', 'exdate'];
            $editable = $recur <= 4 && array_diff(array_keys($options), $allowed) === [];
            $timezone = is_string($options['origin_tz'] ?? null) ? $options['origin_tz'] : '';
            try {
                new \DateTimeZone($timezone);
            } catch (\Exception) {
                $editable = false;
                $timezone = $calendarTimezone;
            }
            $intervalValue = $options['interval'] ?? 1;
            if (!is_int($intervalValue) || $intervalValue < 1 || $intervalValue > 366) {
                $editable = false;
            } else {
                $interval = $intervalValue;
            }
            $anchorValue = $options['origin_start'] ?? null;
            try {
                if (!is_string($anchorValue)) {
                    throw new \InvalidArgumentException();
                }
                $anchor = \DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $anchorValue, new \DateTimeZone($timezone));
                if (!$anchor || $anchor->format('Y-m-d H:i:s') !== $anchorValue) {
                    throw new \InvalidArgumentException();
                }
            } catch (\Exception) {
                $editable = false;
                $anchor = null;
            }
            if ($anchor !== null && $startAt !== null
                && $anchor->getTimestamp() !== $this->bookings->timestamp($startAt)) {
                $editable = false;
            }
            if ($recur === 2) {
                if (array_key_exists('bymonthday', $options)) {
                    $editable = false;
                }
                $weekdays = $options['byday'] ?? ($anchor === null ? [] : [(int) $anchor->format('w')]);
                if (!is_array($weekdays) || $weekdays === [] || !array_is_list($weekdays)
                    || array_filter($weekdays, static fn($day): bool => !is_int($day) || $day < 0 || $day > 6) !== []) {
                    $editable = false;
                    $weekdays = [];
                } else {
                    $weekdays = array_values(array_unique($weekdays));
                    sort($weekdays);
                }
            } elseif (array_key_exists('byday', $options)) {
                $editable = false;
            }
            if ($recur === 3) {
                $monthDay = $options['bymonthday'] ?? ($anchor === null ? null : (int) $anchor->format('j'));
                if (!is_int($monthDay) || $monthDay < 1 || $monthDay > 31) {
                    $editable = false;
                    $monthDay = null;
                }
            } elseif (array_key_exists('bymonthday', $options)) {
                $editable = false;
            }
            if (array_key_exists('exdate', $options) && $options['exdate'] !== []) {
                $editable = false;
            }
            if (isset($options['until'])) {
                if (!is_int($options['until']) || $options['until'] <= 0) {
                    $editable = false;
                } else {
                    $until = (new \DateTimeImmutable('@' . $options['until']))
                        ->setTimezone(new \DateTimeZone($timezone))->format('Y-m-d');
                }
            }
        } elseif ($options !== []) {
            $editable = false;
        }
        return [
            'recur' => $recur,
            'interval' => $interval,
            'weekdays' => $weekdays,
            'month_day' => $monthDay,
            'until' => $until,
            'timezone' => $timezone,
            'editable' => $editable,
        ];
    }

    private function recurrenceOptions(
        int $recur,
        int $interval,
        array $weekdays,
        ?int $monthDay,
        ?string $until,
        string $timezone,
        int $start
    ): array {
        if ($recur < 0 || $recur > 4 || $interval < 1 || $interval > 366) {
            throw new BookingException(__('Choose a supported recurrence and interval.', 'wappointment'));
        }
        if ($recur === 0) {
            return [];
        }
        try {
            $zone = new \DateTimeZone($timezone);
            $anchor = (new \DateTimeImmutable('@' . $start))->setTimezone($zone);
        } catch (\Exception) {
            throw new BookingException(__('The organizer timezone is invalid. Review calendar settings.', 'wappointment'), 409);
        }
        $options = [
            'interval' => $interval,
            'origin_tz' => $timezone,
            'origin_start' => $anchor->format('Y-m-d H:i:s'),
        ];
        if ($recur === 2) {
            if ($weekdays === []) {
                $weekdays = [(int) $anchor->format('w')];
            }
            if (array_filter($weekdays, static fn($day): bool => !is_int($day) || $day < 0 || $day > 6) !== []) {
                throw new BookingException(__('Choose valid weekdays for this recurring time.', 'wappointment'));
            }
            $options['byday'] = array_values(array_unique($weekdays));
            sort($options['byday']);
        } elseif ($weekdays !== []) {
            throw new BookingException(__('Weekdays can only be set for weekly recurrence.', 'wappointment'));
        }
        if ($recur === 3) {
            $monthDay ??= (int) $anchor->format('j');
            if ($monthDay < 1 || $monthDay > 31) {
                throw new BookingException(__('Choose a valid day of the month.', 'wappointment'));
            }
            $options['bymonthday'] = $monthDay;
        } elseif ($monthDay !== null) {
            throw new BookingException(__('A month day can only be set for monthly recurrence.', 'wappointment'));
        }
        if ($until !== null) {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $until, $zone);
            $errors = \DateTimeImmutable::getLastErrors();
            if (!$date || $date->format('Y-m-d') !== $until
                || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
                || $date < $anchor->setTime(0, 0)) {
                throw new BookingException(__('Choose a valid recurrence end date on or after the first occurrence.', 'wappointment'));
            }
            $options['until'] = $date->setTime(23, 59, 59)->getTimestamp();
        }
        return $options;
    }

    private function calendarTimezone(?string $encoded): string
    {
        $options = $this->decodeOptions($encoded);
        $timezone = $options['timezone'] ?? 'UTC';
        if (!is_string($timezone)) {
            throw new BookingException(__('Stored calendar timezone must be reviewed before editing availability.', 'wappointment'), 409);
        }
        try {
            new \DateTimeZone($timezone);
        } catch (\Exception) {
            throw new BookingException(__('Stored calendar timezone must be reviewed before editing availability.', 'wappointment'), 409);
        }
        return $timezone;
    }

    private function decodeOptions(?string $encoded): array
    {
        if ($encoded === null || $encoded === '' || $encoded === '{}') {
            return [];
        }
        try {
            $options = json_decode($encoded, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $error) {
            error_log('Wappointment: invalid stored availability status options: ' . $error->getMessage());
            throw new BookingException(__('Stored availability data needs repair. Please contact the site administrator.', 'wappointment'), 503);
        }
        if ($options === []) {
            return [];
        }
        if (!is_array($options) || array_is_list($options)) {
            throw new BookingException(__('Stored availability data must be an object. Please contact the site administrator.', 'wappointment'), 503);
        }
        return $options;
    }

    private function encodeOptions(array $options): string
    {
        return wp_json_encode($options === [] ? new \stdClass() : $options, JSON_THROW_ON_ERROR);
    }

    private function hasConditions(?string $encoded): bool
    {
        if ($encoded === null || $encoded === '') {
            return false;
        }
        try {
            $options = json_decode($encoded, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $error) {
            error_log('Wappointment: invalid stored availability status options: ' . $error->getMessage());
            throw new BookingException(__('Stored availability data needs repair. Please contact the site administrator.', 'wappointment'), 503);
        }
        if ($options === []) {
            return false;
        }
        if (!$options instanceof \stdClass) {
            throw new BookingException(__('Stored availability data must be an object. Please contact the site administrator.', 'wappointment'), 503);
        }
        return property_exists($options, 'conditions');
    }

    private function rows(string $sql): array
    {
        $rows = $this->db->get_results($sql, ARRAY_A);
        if ($this->db->last_error !== '') {
            $this->failure();
        }
        return $rows ?? [];
    }

    private function statusTable(): string
    {
        return $this->db->prefix . 'wappo_statuses';
    }

    private function calendarTable(): string
    {
        return $this->db->prefix . 'wappo_calendars';
    }

    private function failure(): never
    {
        error_log('Wappointment availability status database error: ' . $this->db->last_error);
        throw new BookingException(__('Availability exceptions could not be saved or loaded. Please contact the site administrator.', 'wappointment'), 503);
    }
}
