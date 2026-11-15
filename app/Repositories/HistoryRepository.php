<?php
declare(strict_types=1);

namespace Wappointment\Repositories;

class HistoryRepository
{
    private \wpdb $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function customerAppointments(string $email): array
    {
        return $this->appointments(
            "c.email = %s AND c.deleted_at IS NULL",
            [$email]
        );
    }

    public function staffAppointments(int $userId): array
    {
        return $this->appointments(
            'owner.wp_uid = %d AND owner.deleted_at IS NULL',
            [$userId]
        );
    }

    private function appointments(string $scope, array $parameters): array
    {
        $prefix = $this->db->prefix . 'wappo_';
        $sql = $this->db->prepare(
            "SELECT a.id, a.start_at, a.end_at, a.status, a.edit_key, a.options AS appointment_options,
                    c.name AS client_name, c.email AS client_email, c.options AS client_options,
                    s.name AS service_name, owner.name AS calendar_name, owner.options AS calendar_options,
                    l.name AS location_name
             FROM {$prefix}appointments a
             JOIN {$prefix}clients c ON c.id = a.client_id
             JOIN {$prefix}services s ON s.id = a.service_id
             JOIN {$prefix}calendars owner ON owner.id = a.staff_id
             LEFT JOIN {$prefix}locations l ON l.id = a.location_id
             WHERE {$scope}
             ORDER BY a.start_at DESC, a.id DESC",
            ...$parameters
        );
        $rows = $this->db->get_results($sql, ARRAY_A);
        if ($this->db->last_error !== '' || !is_array($rows)) {
            throw new \RuntimeException('Could not load appointment history. ' . $this->db->last_error);
        }
        return array_map(static function (array $row): array {
            $calendarOptions = self::decodeOptions($row['calendar_options']);
            $clientOptions = self::decodeOptions($row['client_options']);
            $calendarTimezone = $calendarOptions['timezone'] ?? 'UTC';
            $customerTimezone = $clientOptions['tz'] ?? '';
            return [
                'id' => (int) $row['id'],
                'start_at' => $row['start_at'],
                'end_at' => $row['end_at'],
                'status' => (int) $row['status'],
                'edit_key' => $row['edit_key'],
                'client_name' => $row['client_name'],
                'client_email' => $row['client_email'],
                'client_phone' => (string) ($clientOptions['phone'] ?? ''),
                'customer_timezone' => is_string($customerTimezone) ? $customerTimezone : '',
                'service_name' => $row['service_name'],
                'calendar_name' => $row['calendar_name'],
                'calendar_timezone' => is_string($calendarTimezone) ? $calendarTimezone : 'UTC',
                'location_name' => $row['location_name'] ?? '',
            ];
        }, $rows);
    }

    private static function decodeOptions(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }
        $options = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($options)) {
            throw new \RuntimeException('Could not read appointment history because stored options are not an object.');
        }
        return $options;
    }
}
