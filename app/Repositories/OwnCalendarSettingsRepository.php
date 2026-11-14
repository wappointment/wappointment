<?php
declare(strict_types=1);

namespace Wappointment\Repositories;

use Wappointment\Booking\WeeklySchedule;
use Wappointment\Http\Data\CalendarWeeklySettingsData;

class OwnCalendarSettingsRepository
{
    private \wpdb $db;

    public function __construct(private BookingRepository $bookings)
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function all(int $owner): array
    {
        $table = $this->table();
        return array_map($this->present(...), $this->rows($this->db->prepare(
            "SELECT id, name, options FROM {$table} WHERE wp_uid = %d AND deleted_at IS NULL ORDER BY sorting, id",
            $owner
        )));
    }

    public function save(int $id, int $owner, CalendarWeeklySettingsData $settings): array
    {
        return $this->bookings->exclusive(function () use ($id, $owner, $settings): array {
            $table = $this->table();
            $rows = $this->rows($this->db->prepare(
                "SELECT id, name, options FROM {$table} WHERE id = %d AND wp_uid = %d AND deleted_at IS NULL FOR UPDATE",
                $id, $owner
            ));
            if (!$rows) {
                throw new \RuntimeException(__('Calendar not found.', 'wappointment'), 404);
            }
            $calendar = $rows[0];
            if ($this->present($calendar)['problem'] !== null) {
                throw new \RuntimeException(__('Stored weekly settings must be migrated before editing this calendar. Nothing has been changed.', 'wappointment'), 409);
            }
            $options = $this->options($calendar);
            $options->timezone = $settings->timezone;
            $options->avb = $settings->booking_days;
            $options->regav = $settings->regav->toArray() + ['precise' => true];
            $calendar['options'] = json_encode($options, JSON_THROW_ON_ERROR);
            if ($this->db->update($table, [
                'options' => $calendar['options'], 'updated_at' => gmdate('Y-m-d H:i:s'),
            ], ['id' => $id]) === false) {
                $this->failure();
            }
            return $this->present($calendar);
        });
    }

    private function present(array $calendar): array
    {
        $options = get_object_vars($this->options($calendar));
        $settings = null;
        $problem = null;
        $days = array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], []);
        try {
            $regav = array_key_exists('regav', $options)
                ? json_decode(json_encode($options['regav'], JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR)
                : $days;
            if (!is_array($regav) || ($regav !== [] && array_is_list($regav))) {
                throw new \InvalidArgumentException('Unsupported weekly schedule shape.');
            }
            $settings = CalendarWeeklySettingsData::from([
                'timezone' => array_key_exists('timezone', $options) ? $options['timezone'] : 'UTC',
                'booking_days' => array_key_exists('avb', $options) ? $options['avb'] : 60,
                'regav' => WeeklySchedule::minutes($regav),
            ])->toArray();
        } catch (\InvalidArgumentException $error) {
            $problem = __('This calendar has unsupported weekly settings or availability conditions. They must be migrated before editing. Stored data has not been changed.', 'wappointment');
        }
        return ['id' => (int) $calendar['id'], 'name' => $calendar['name'], 'settings' => $settings, 'problem' => $problem];
    }

    private function options(array $calendar): \stdClass
    {
        try {
            $options = $calendar['options'] === null ? new \stdClass() : json_decode($calendar['options'], false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $error) {
            error_log('Wappointment: invalid stored calendar options: ' . $error->getMessage());
            throw new \RuntimeException(__('Stored calendar settings need repair. Please contact the site administrator.', 'wappointment'), 500);
        }
        if ($options === []) {
            return new \stdClass();
        }
        if (!$options instanceof \stdClass) {
            throw new \RuntimeException(__('Stored calendar settings must be an object. Please contact the site administrator.', 'wappointment'), 500);
        }
        return $options;
    }

    private function table(): string
    {
        return $this->db->prefix . 'wappo_calendars';
    }

    private function rows(string $sql): array
    {
        $rows = $this->db->get_results($sql, ARRAY_A);
        if ($this->db->last_error !== '') {
            $this->failure();
        }
        return $rows ?? [];
    }

    private function failure(): never
    {
        error_log('Wappointment calendar settings database error: ' . $this->db->last_error);
        throw new \RuntimeException(__('Calendar settings could not be saved or loaded. Please contact the site administrator.', 'wappointment'), 500);
    }
}
