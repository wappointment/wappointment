<?php
declare(strict_types=1);

namespace Wappointment\Repositories;

use Wappointment\Booking\BookingException;
use Wappointment\Booking\RecurringStatusOccurrences;

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
        $recurringStatuses = new RecurringStatusOccurrences();
        foreach ($statuses as $status) {
            if (!empty($status['options']['conditions'])) {
                throw new BookingException(__('Conditional availability must be migrated before this calendar can accept bookings.', 'wappointment'), 409);
            }
            $intervals = (int) $status['recur'] === 0
                ? [[$this->timestamp($status['start_at']), $this->timestamp($status['end_at'])]]
                : $recurringStatuses->expand($status, $from, $to);
            foreach ($intervals as $interval) {
                if ((int) $status['type'] === 0) {
                    $extra[] = $interval;
                } else {
                    $busy[] = $interval;
                }
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

    public function adminAppointments(?int $owner = null, array $filters = []): array
    {
        $where = [];
        $params = [];
        if ($owner !== null) {
            $where[] = "EXISTS (SELECT 1 FROM {$this->prefix()}calendars owner
                                WHERE owner.id = a.staff_id AND owner.wp_uid = %d AND owner.deleted_at IS NULL)";
            $params[] = $owner;
        }
        if (isset($filters['from'], $filters['to'])) {
            $where[] = 'a.start_at < %s AND a.end_at > %s';
            $params[] = gmdate('Y-m-d H:i:s', $filters['to']);
            $params[] = gmdate('Y-m-d H:i:s', $filters['from']);
        }
        if (isset($filters['calendar_id'])) {
            $where[] = 'a.staff_id = %d';
            $params[] = $filters['calendar_id'];
        }
        if (isset($filters['service_id'])) {
            $where[] = 'a.service_id = %d';
            $params[] = $filters['service_id'];
        }
        $clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($filters['per_page'] ?? 100)));
        $offset = ($page - 1) * $perPage;
        $order = isset($filters['from']) ? 'ASC' : 'DESC';
        $countSql = "SELECT COUNT(*) FROM {$this->prefix()}appointments a{$clause}";
        $count = $this->db->get_var($params
            ? $this->db->prepare($countSql, ...$params)
            : $countSql);
        $this->checkError();
        $total = (int) $count;
        $query = "SELECT a.id, a.staff_id, a.service_id, a.location_id, a.start_at, a.end_at, a.status,
                         c.name AS client_name, c.email AS client_email, s.name AS service_name, a.options
                  FROM {$this->prefix()}appointments a
                  LEFT JOIN {$this->prefix()}clients c ON c.id = a.client_id
                  LEFT JOIN {$this->prefix()}services s ON s.id = a.service_id
                  {$clause} ORDER BY a.start_at {$order}, a.id {$order} LIMIT %d OFFSET %d";
        $params[] = $perPage;
        $params[] = $offset;
        $rows = $this->rows($this->db->prepare($query, ...$params));
        $filtersData = $this->adminAppointmentFilters($owner);
        $events = [];
        foreach ($rows as $appointment) {
            $status = (int) $appointment['status'];
            $type = $status < 0
                ? 'appointment_cancelled'
                : ($status === 1 ? 'appointment_confirmed' : 'appointment_pending');
            $events[] = [
                'id' => 'appointment-' . $appointment['id'],
                'type' => $type,
                'title' => trim(($appointment['client_name'] ?? '') . ' - ' . ($appointment['service_name'] ?? 'Appointment'), ' -'),
                'start_at' => $appointment['start_at'],
                'end_at' => $appointment['end_at'],
                'appointment_id' => (int) $appointment['id'],
            ];
        }
        if (isset($filters['from'], $filters['to'])) {
            array_push($events, ...$this->adminAvailabilityEvents($owner, $filters));
        }
        usort($events, static fn(array $left, array $right): int => strcmp($left['start_at'], $right['start_at']));
        return [
            'appointments' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'calendars' => $filtersData['calendars'],
            'services' => $filtersData['services'],
            'locations' => $filtersData['locations'],
            'events' => $events,
        ];
    }

    private function adminAppointmentFilters(?int $owner): array
    {
        $ownerCalendar = $owner === null ? '' : $this->db->prepare(
            ' AND c.wp_uid = %d',
            $owner
        );
        $calendarRows = $this->rows(
            "SELECT c.id, c.name, c.options FROM {$this->prefix()}calendars c
             WHERE c.deleted_at IS NULL{$ownerCalendar}
             ORDER BY c.sorting, c.id"
        );
        foreach ($calendarRows as &$calendar) {
            $timezone = $calendar['options']['timezone'] ?? 'UTC';
            $calendar['timezone'] = is_string($timezone) && in_array($timezone, \DateTimeZone::listIdentifiers(), true)
                ? $timezone
                : 'UTC';
            unset($calendar['options']);
        }
        unset($calendar);
        $serviceScope = $owner === null ? '' : $this->db->prepare(
            ' AND c.wp_uid = %d',
            $owner
        );
        $serviceRows = $this->rows(
            "SELECT DISTINCT s.id, s.name, s.options FROM {$this->prefix()}services s
             JOIN {$this->prefix()}calendar_service cs ON cs.service_id = s.id
             JOIN {$this->prefix()}calendars c ON c.id = cs.calendar_id
             WHERE s.deleted_at IS NULL AND c.deleted_at IS NULL{$serviceScope}
             ORDER BY s.sorting, s.id"
        );
        $locationRows = $this->rows(
            "SELECT l.id, l.name, l.type FROM {$this->prefix()}locations l
             WHERE l.deleted_at IS NULL ORDER BY l.id"
        );
        return [
            'calendars' => $calendarRows,
            'services' => $serviceRows,
            'locations' => $locationRows,
        ];
    }

    private function adminAvailabilityEvents(?int $owner, array $filters): array
    {
        $where = ['s.muted = 0', 's.start_at < %s', '(s.end_at > %s OR s.recur > 0)'];
        $params = [
            gmdate('Y-m-d H:i:s', $filters['to']),
            gmdate('Y-m-d H:i:s', $filters['from']),
        ];
        if ($owner !== null) {
            $where[] = 'c.wp_uid = %d AND c.deleted_at IS NULL';
            $params[] = $owner;
        }
        if (isset($filters['calendar_id'])) {
            $where[] = 's.staff_id = %d';
            $params[] = $filters['calendar_id'];
        }
        if (isset($filters['service_id'])) {
            $where[] = "EXISTS (
                SELECT 1 FROM {$this->prefix()}calendar_service cs
                WHERE cs.calendar_id = s.staff_id AND cs.service_id = %d
            )";
            $params[] = $filters['service_id'];
        }
        $statuses = $this->rows($this->db->prepare(
            "SELECT s.* FROM {$this->prefix()}statuses s
             LEFT JOIN {$this->prefix()}calendars c ON c.id = s.staff_id
             WHERE " . implode(' AND ', $where),
            ...$params
        ));
        $recurrence = new RecurringStatusOccurrences();
        $events = [];
        foreach ($statuses as $status) {
            $occurrences = (int) $status['recur'] > 0
                ? $recurrence->expand($status, $filters['from'], $filters['to'])
                : [[
                    $this->timestamp($status['start_at']),
                    $this->timestamp($status['end_at']),
                ]];
            foreach ($occurrences as [$start, $end]) {
                $external = !empty($status['source']);
                $available = (int) $status['type'] === 0;
                $events[] = [
                    'id' => 'status-' . $status['id'] . '-' . $start,
                    'type' => $external ? 'external' : ($available ? 'availability_free' : 'availability_busy'),
                    'title' => $external ? __('External calendar', 'wappointment')
                        : ($available ? __('Available', 'wappointment') : __('Busy', 'wappointment')),
                    'start_at' => gmdate('Y-m-d H:i:s', $start),
                    'end_at' => gmdate('Y-m-d H:i:s', $end),
                    'appointment_id' => null,
                ];
            }
        }
        return $events;
    }

    public function adminAppointment(int $id, ?int $owner = null, bool $lock = false): array
    {
        $sql = $owner === null
            ? $this->db->prepare("SELECT a.* FROM {$this->prefix()}appointments a WHERE a.id = %d", $id)
            : $this->db->prepare(
                "SELECT a.* FROM {$this->prefix()}appointments a
                 JOIN {$this->prefix()}calendars owner ON owner.id = a.staff_id
                 WHERE a.id = %d AND owner.wp_uid = %d AND owner.deleted_at IS NULL",
                $id,
                $owner
            );
        $rows = $this->rows($sql . ($lock ? ' FOR UPDATE' : ''));
        if (!$rows) {
            throw new BookingException(__('Appointment not found.', 'wappointment'), 404);
        }
        return $rows[0];
    }

    public function adminBookingClient(int $id, ?int $owner): array
    {
        $scope = $owner === null ? '' : $this->db->prepare(
            " AND EXISTS (
                SELECT 1 FROM {$this->prefix()}appointments a
                JOIN {$this->prefix()}calendars owner ON owner.id = a.staff_id
                WHERE a.client_id = c.id AND owner.wp_uid = %d AND owner.deleted_at IS NULL
            )",
            $owner
        );
        $rows = $this->rows($this->db->prepare(
            "SELECT c.* FROM {$this->prefix()}clients c
             WHERE c.id = %d AND c.deleted_at IS NULL{$scope} LIMIT 1",
            $id
        ));
        if (!$rows) {
            throw new BookingException(__('Client not found.', 'wappointment'), 404);
        }
        return $rows[0];
    }

    public function assertCalendarOwned(int $calendarId, ?int $owner): void
    {
        if ($owner === null) {
            return;
        }
        $count = $this->db->get_var($this->db->prepare(
            "SELECT COUNT(*) FROM {$this->prefix()}calendars
             WHERE id = %d AND wp_uid = %d AND deleted_at IS NULL",
            $calendarId,
            $owner
        ));
        $this->checkError();
        if ((int) $count !== 1) {
            throw new BookingException(__('Calendar not found.', 'wappointment'), 404);
        }
    }

    public function setAppointmentLocked(int $id, bool $locked, ?int $owner): void
    {
        $this->exclusive(function () use ($id, $locked, $owner): void {
            $appointment = $this->adminAppointment($id, $owner, true);
            $this->assertStandaloneAppointment($appointment);
            $options = $appointment['options'];
            if ($locked) {
                $options['locked'] = true;
            } else {
                unset($options['locked']);
            }
            $this->updateAppointment($id, [
                'options' => wp_json_encode($options, JSON_THROW_ON_ERROR),
                'updated_at' => gmdate('Y-m-d H:i:s'),
            ]);
        });
    }

    public function deleteAdminAppointment(int $id, ?int $owner, bool $force): void
    {
        $this->exclusive(function () use ($id, $owner, $force): void {
            $appointment = $this->adminAppointment($id, $owner, true);
            $this->assertStandaloneAppointment($appointment);
            if (!empty($appointment['options']['locked']) && !$force) {
                throw new BookingException(__('This appointment is locked. Force deletion requires manager access.', 'wappointment'), 409);
            }
            if (!empty($appointment['options']['providers'])) {
                throw new BookingException(__('This appointment is linked to an external provider that v3 cannot safely update.', 'wappointment'), 409);
            }
            $updated = $this->db->update(
                $this->prefix() . 'order_price',
                ['appointment_id' => null],
                ['appointment_id' => $id]
            );
            if ($updated === false) {
                $this->failure();
            }
            if ($this->db->delete($this->prefix() . 'appointments', ['id' => $id]) === false) {
                $this->failure();
            }
        });
    }

    private function assertStandaloneAppointment(array $appointment): void
    {
        if (!empty($appointment['recurrent']) || !empty($appointment['parent'])) {
            throw new BookingException(__('Recurring appointment operations are unavailable until Group support is migrated.', 'wappointment'), 409);
        }
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

    public function nextCanceledStatus(array $appointment): int
    {
        $rows = $this->rows($this->db->prepare(
            "SELECT status FROM {$this->prefix()}appointments
             WHERE staff_id = %d AND start_at = %s AND end_at = %s AND status < 0 AND id <> %d",
            (int) $appointment['staff_id'],
            $appointment['start_at'],
            $appointment['end_at'],
            (int) $appointment['id']
        ));
        // The existing unique index includes status; distinct negative states preserve same-slot history.
        $used = array_fill_keys(array_map(static fn(array $row): int => (int) $row['status'], $rows), true);
        for ($status = -1; $status >= -128; $status--) {
            if (!isset($used[$status])) {
                return $status;
            }
        }

        throw new BookingException(__('This appointment cannot be canceled because its slot has exhausted the available canceled states.', 'wappointment'), 409);
    }

    public function exclusive(callable $operation): mixed
    {
        $name = 'wappo_booking_' . substr(hash('sha256', DB_NAME . $this->prefix()), 0, 40);
        if ((string) $this->db->get_var($this->db->prepare('SELECT GET_LOCK(%s, 5)', $name)) !== '1') {
            throw new BookingException(__('Booking is busy. Please try again.', 'wappointment'), 503);
        }
        try {
            foreach (['appointments', 'clients', 'services', 'calendars', 'locations', 'statuses', 'calendar_service', 'service_location'] as $suffix) {
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
