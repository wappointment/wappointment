<?php
declare(strict_types=1);

namespace Wappointment\Repositories;

use Wappointment\Booking\BookingException;

class BookingRepository
{
    private \wpdb $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    private function prefix(): string
    {
        return $this->db->prefix . 'wappo_';
    }

    public function catalog(): array
    {
        $services = $this->rows("SELECT * FROM {$this->prefix()}services WHERE deleted_at IS NULL ORDER BY sorting, id LIMIT 3");
        $calendars = $this->rows("SELECT * FROM {$this->prefix()}calendars WHERE deleted_at IS NULL AND status > 0 ORDER BY sorting, id LIMIT 2");
        $locations = $this->rows("SELECT * FROM {$this->prefix()}locations WHERE deleted_at IS NULL AND status >= 0 ORDER BY id");
        return [
            'services' => $services,
            'calendars' => $calendars,
            'locations' => $locations,
            'calendar_service' => $this->rows("SELECT calendar_id, service_id FROM {$this->prefix()}calendar_service"),
            'service_location' => $this->rows("SELECT service_id, location_id FROM {$this->prefix()}service_location"),
        ];
    }

    public function intervals(int $calendar, int $from, int $to, int $exclude = 0): array
    {
        $appointments = $this->rows($this->db->prepare(
            "SELECT start_at, end_at FROM {$this->prefix()}appointments
             WHERE staff_id = %d AND status >= 0 AND id <> %d AND start_at < %s AND end_at > %s",
            $calendar, $exclude, gmdate('Y-m-d H:i:s', $to), gmdate('Y-m-d H:i:s', $from)
        ));
        $statuses = $this->rows($this->db->prepare(
            "SELECT * FROM {$this->prefix()}statuses
             WHERE staff_id = %d AND muted = 0 AND start_at < %s AND (end_at > %s OR recur > 0)",
            $calendar, gmdate('Y-m-d H:i:s', $to), gmdate('Y-m-d H:i:s', $from)
        ));
        $busy = array_map(fn(array $row): array => [$this->timestamp($row['start_at']), $this->timestamp($row['end_at'])], $appointments);
        $extra = [];
        foreach ($statuses as $status) {
            if ((int) $status['recur'] !== 0 || !empty($status['options']['conditions'])) {
                throw new BookingException(__('Recurring or conditional availability must be migrated before this calendar can accept bookings.', 'wappointment'), 409);
            }
            $interval = [$this->timestamp($status['start_at']), $this->timestamp($status['end_at'])];
            if ((int) $status['type'] === 0) {
                $extra[] = $interval;
            } else {
                $busy[] = $interval;
            }
        }
        return [$busy, $extra];
    }

    public function appointment(string $key): array
    {
        $rows = $this->rows($this->db->prepare(
            "SELECT * FROM {$this->prefix()}appointments WHERE edit_key = %s LIMIT 1", $key
        ));
        if (!$rows) {
            throw new BookingException(__('Appointment not found.', 'wappointment'), 404);
        }
        return $rows[0];
    }

    public function client(array $data): int
    {
        $rows = $this->rows($this->db->prepare(
            "SELECT * FROM {$this->prefix()}clients WHERE email = %s LIMIT 1", $data['email']
        ));
        if ($rows) {
            // Public bookings must not overwrite an existing customer's name or private fields.
            if ($rows[0]['deleted_at'] !== null) {
                if ($this->db->update($this->prefix() . 'clients', [
                    'deleted_at' => null, 'updated_at' => gmdate('Y-m-d H:i:s'),
                ], ['id' => (int) $rows[0]['id']]) === false) {
                    $this->failure();
                }
            }
            return (int) $rows[0]['id'];
        }
        return $this->insert('clients', [
            'email' => $data['email'], 'name' => $data['name'],
            'options' => wp_json_encode(['phone' => $data['phone'] ?? '']),
            'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    public function activeBookings(int $client, ?int $calendar, int $now): int
    {
        $sql = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->prefix()}appointments WHERE client_id = %d AND status >= 0 AND start_at >= %s",
            $client, gmdate('Y-m-d H:i:s', $now)
        );
        if ($calendar !== null) {
            $sql .= $this->db->prepare(' AND staff_id = %d', $calendar);
        }
        $count = $this->db->get_var($sql);
        $this->checkError();
        return (int) $count;
    }

    public function configure(array $service, array $calendar, array $location): array
    {
        return $this->exclusive(function () use ($service, $calendar, $location): array {
            $catalog = $this->catalog();
            $serviceId = $this->saveConfigurationRow('services', $service, $catalog['services'][0] ?? null);
            $calendarId = $this->saveConfigurationRow('calendars', $calendar, $catalog['calendars'][0] ?? null);
            $locationId = $this->saveConfigurationRow('locations', $location, $catalog['locations'][0] ?? null);
            foreach ([
                ['calendar_service', ['calendar_id' => $calendarId, 'service_id' => $serviceId]],
                ['service_location', ['service_id' => $serviceId, 'location_id' => $locationId]],
            ] as [$table, $relation]) {
                $where = [];
                foreach ($relation as $column => $value) {
                    $where[] = $this->db->prepare("{$column} = %d", $value);
                }
                $count = $this->db->get_var("SELECT COUNT(*) FROM {$this->prefix()}{$table} WHERE " . implode(' AND ', $where));
                $this->checkError();
                if (!(int) $count && $this->db->insert($this->prefix() . $table, $relation) === false) {
                    $this->failure();
                }
            }
            return ['service_id' => $serviceId, 'calendar_id' => $calendarId, 'location_id' => $locationId];
        });
    }

    public function adminAppointments(?int $owner = null): array
    {
        $scope = $owner === null ? '' : $this->db->prepare(
            " WHERE EXISTS (SELECT 1 FROM {$this->prefix()}calendars owner
                            WHERE owner.id = a.staff_id AND owner.wp_uid = %d AND owner.deleted_at IS NULL)",
            $owner
        );
        return $this->rows(
            "SELECT a.id, a.start_at, a.end_at, a.status, c.name AS client_name, c.email AS client_email,
                    s.name AS service_name, a.options
             FROM {$this->prefix()}appointments a
             LEFT JOIN {$this->prefix()}clients c ON c.id = a.client_id
             LEFT JOIN {$this->prefix()}services s ON s.id = a.service_id
             {$scope} ORDER BY a.start_at DESC LIMIT 100"
        );
    }

    public function setStatus(int $id, int $status, ?int $owner = null): void
    {
        $this->exclusive(function () use ($id, $status, $owner): void {
            $sql = $owner === null
                ? $this->db->prepare("SELECT a.* FROM {$this->prefix()}appointments a WHERE a.id = %d FOR UPDATE", $id)
                : $this->db->prepare(
                    "SELECT a.* FROM {$this->prefix()}appointments a
                     JOIN {$this->prefix()}calendars owner ON owner.id = a.staff_id
                     WHERE a.id = %d AND owner.wp_uid = %d AND owner.deleted_at IS NULL FOR UPDATE", $id, $owner
                );
            $rows = $this->rows($sql);
            if (!$rows) {
                throw new BookingException(__('Appointment not found.', 'wappointment'), 404);
            }
            if (!empty($rows[0]['recurrent']) || !empty($rows[0]['parent'])) {
                throw new BookingException(__('Recurring appointments must be migrated before editing.', 'wappointment'), 409);
            }
            if ($status === 1 && (int) $rows[0]['status'] !== 0) {
                throw new BookingException(__('Only pending appointments can be confirmed.', 'wappointment'), 409);
            }
            $this->updateAppointment($id, ['status' => $status, 'updated_at' => gmdate('Y-m-d H:i:s')]);
        });
    }

    public function insert(string $table, array $data): int
    {
        $this->assertWritableTable($table);
        if ($this->db->insert($this->prefix() . $table, $data) === false) {
            $this->failure();
        }
        return (int) $this->db->insert_id;
    }

    public function updateAppointment(int $id, array $data): void
    {
        if ($this->db->update($this->prefix() . 'appointments', $data, ['id' => $id]) === false) {
            $this->failure();
        }
    }

    public function exclusive(callable $operation): mixed
    {
        $name = 'wappo_booking_' . substr(hash('sha256', DB_NAME . $this->prefix()), 0, 40);
        if ((string) $this->db->get_var($this->db->prepare('SELECT GET_LOCK(%s, 5)', $name)) !== '1') {
            throw new BookingException(__('Booking is busy. Please try again.', 'wappointment'), 503);
        }
        try {
            foreach (['appointments', 'clients', 'services', 'calendars', 'locations', 'calendar_service', 'service_location'] as $suffix) {
                $table = $this->prefix() . $suffix;
                $engine = $this->db->get_row($this->db->prepare('SHOW TABLE STATUS LIKE %s', $this->db->esc_like($table)), ARRAY_A);
                if (!$engine || strcasecmp($engine['Engine'], 'InnoDB') !== 0) {
                    throw new BookingException(__('Booking requires InnoDB tables. Please contact the site administrator.', 'wappointment'), 503);
                }
            }
            if ($this->db->query('START TRANSACTION') === false) {
                $this->failure();
            }
            try {
                $result = $operation();
                if ($this->db->query('COMMIT') === false) {
                    $this->failure();
                }
                return $result;
            } catch (\Throwable $error) {
                if ($this->db->query('ROLLBACK') === false) {
                    error_log('Wappointment: booking rollback failed: ' . $this->db->last_error);
                }
                throw $error;
            }
        } finally {
            if ((string) $this->db->get_var($this->db->prepare('SELECT RELEASE_LOCK(%s)', $name)) !== '1') {
                error_log('Wappointment: could not release booking lock.');
            }
        }
    }

    public function timestamp(string $value): int
    {
        return (new \DateTimeImmutable($value, new \DateTimeZone('UTC')))->getTimestamp();
    }

    private function rows(string $sql): array
    {
        $rows = $this->db->get_results($sql, ARRAY_A);
        $this->checkError();
        $rows ??= [];
        foreach ($rows as &$row) {
            if (array_key_exists('options', $row)) {
                try {
                    $row['options'] = $row['options'] === null ? [] : json_decode($row['options'], true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $error) {
                    error_log('Wappointment: invalid stored booking options: ' . $error->getMessage());
                    throw new BookingException(__('Stored booking data needs repair. Please contact the site administrator.', 'wappointment'), 503);
                }
                if (!is_array($row['options'])) {
                    throw new BookingException(__('Stored booking options must be an object.', 'wappointment'), 503);
                }
            }
        }
        return $rows;
    }

    private function assertWritableTable(string $table): void
    {
        if (!in_array($table, ['clients', 'appointments', 'services', 'calendars', 'locations'], true)) {
            throw new \InvalidArgumentException('Unsupported booking table.');
        }
    }

    private function checkError(): void
    {
        if ($this->db->last_error !== '') {
            $this->failure();
        }
    }

    private function saveConfigurationRow(string $table, array $data, ?array $existing): int
    {
        $this->assertWritableTable($table);
        $data['options'] = wp_json_encode(array_merge($existing['options'] ?? [], $data['options']));
        $data['updated_at'] = gmdate('Y-m-d H:i:s');
        if ($existing) {
            if ($this->db->update($this->prefix() . $table, $data, ['id' => (int) $existing['id']]) === false) {
                $this->failure();
            }
            return (int) $existing['id'];
        }
        return $this->insert($table, array_merge($data, ['created_at' => gmdate('Y-m-d H:i:s')]));
    }

    private function failure(): never
    {
        error_log('Wappointment booking database error: ' . $this->db->last_error);
        throw new BookingException(__('Could not save or load booking data. Please try again or contact the organizer.', 'wappointment'), 503);
    }
}
