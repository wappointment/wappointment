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

    public function all(int $calendarId, int $owner): array
    {
        $this->assertOwnedCalendar($calendarId, $owner);
        $rows = $this->rows($this->db->prepare(
            "SELECT id, type, start_at, end_at, options FROM {$this->statusTable()}
             WHERE staff_id = %d AND source IS NULL AND recur = 0 ORDER BY start_at, id",
            $calendarId
        ));
        $statuses = [];
        foreach ($rows as $row) {
            if ($this->hasConditions($row['options'])) {
                continue;
            }
            $statuses[] = $this->present($row);
        }
        return $statuses;
    }

    public function create(int $calendarId, int $owner, int $type, int $start, int $end): array
    {
        return $this->bookings->exclusive(function () use ($calendarId, $owner, $type, $start, $end): array {
            $this->lockOwnedCalendar($calendarId, $owner);
            $data = [
                'staff_id' => $calendarId,
                'type' => $type,
                'start_at' => gmdate('Y-m-d H:i:s', $start),
                'end_at' => gmdate('Y-m-d H:i:s', $end),
                'recur' => 0,
                'muted' => 0,
                'source' => null,
                'eventkey' => null,
                'options' => '{}',
            ];
            if ($this->db->insert($this->statusTable(), $data) === false) {
                $this->failure();
            }
            return ['id' => (int) $this->db->insert_id, 'type' => $type, 'start' => $start, 'end' => $end];
        });
    }

    public function delete(int $calendarId, int $statusId, int $owner): void
    {
        $this->bookings->exclusive(function () use ($calendarId, $statusId, $owner): void {
            $this->lockOwnedCalendar($calendarId, $owner);
            $rows = $this->rows($this->db->prepare(
                "SELECT id, recur, source, options FROM {$this->statusTable()}
                 WHERE id = %d AND staff_id = %d FOR UPDATE",
                $statusId, $calendarId
            ));
            if (!$rows) {
                throw new BookingException(__('Availability exception not found.', 'wappointment'), 404);
            }
            $status = $rows[0];
            if ((int) $status['recur'] !== 0 || $status['source'] !== null || $this->hasConditions($status['options'])) {
                throw new BookingException(__('This availability exception is managed by another feature and cannot be deleted here.', 'wappointment'), 409);
            }
            if ($this->db->delete($this->statusTable(), ['id' => $statusId, 'staff_id' => $calendarId]) === false) {
                $this->failure();
            }
        });
    }

    private function assertOwnedCalendar(int $calendarId, int $owner): void
    {
        $rows = $this->rows($this->db->prepare(
            "SELECT id FROM {$this->calendarTable()} WHERE id = %d AND wp_uid = %d AND deleted_at IS NULL",
            $calendarId, $owner
        ));
        if (!$rows) {
            throw new BookingException(__('Calendar not found.', 'wappointment'), 404);
        }
    }

    private function lockOwnedCalendar(int $calendarId, int $owner): void
    {
        $rows = $this->rows($this->db->prepare(
            "SELECT id FROM {$this->calendarTable()} WHERE id = %d AND wp_uid = %d AND deleted_at IS NULL FOR UPDATE",
            $calendarId, $owner
        ));
        if (!$rows) {
            throw new BookingException(__('Calendar not found.', 'wappointment'), 404);
        }
    }

    private function present(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'type' => (int) $row['type'],
            'start' => $this->bookings->timestamp($row['start_at']),
            'end' => $this->bookings->timestamp($row['end_at']),
        ];
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
