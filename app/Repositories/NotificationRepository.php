<?php
declare(strict_types=1);

namespace Wappointment\Repositories;

/** Read-only lookups for booking notifications over the existing tables. */
class NotificationRepository
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

    /**
     * Legacy event codes of published email reminders. Their presence is the stored
     * per-event switch for customer notifications.
     *
     * @return list<int>
     */
    public function publishedEmailReminderEvents(): array
    {
        $events = $this->db->get_col(
            "SELECT DISTINCT event FROM {$this->prefix()}reminders WHERE published = 1 AND type = 1"
        );
        $this->checkError();
        return array_map('intval', $events ?? []);
    }

    /**
     * The published email template for a legacy event code, preferring the client's
     * locale (legacy `PreparesClientEmail::tryToLoadEmail`: the localized row first,
     * otherwise the first published row for the event whatever its language).
     *
     * @return array{id: int, subject: string, body: mixed}|null
     */
    public function emailTemplate(int $event, string $locale): ?array
    {
        $row = $this->db->get_row($this->db->prepare(
            "SELECT id, subject, options FROM {$this->prefix()}reminders
             WHERE published = 1 AND type = 1 AND event = %d
             ORDER BY (lang = %s) DESC, id ASC LIMIT 1",
            $event,
            $locale
        ), ARRAY_A);
        $this->checkError();
        if (!$row) {
            return null;
        }
        $options = $this->object($row['options']);
        return [
            'id' => (int) $row['id'],
            'subject' => (string) ($row['subject'] ?? ''),
            'body' => $options['body'] ?? null,
        ];
    }

    /**
     * Published "before the appointment" email reminders (`event = 1`). Translations
     * (rows with a `parent`) are not scheduled separately; delivery picks the
     * translation of the scheduled reminder that matches the client's locale.
     *
     * @return list<array{id: int, options: array}>
     */
    public function publishedTimedReminders(): array
    {
        $rows = $this->db->get_results(
            "SELECT id, options FROM {$this->prefix()}reminders
             WHERE published = 1 AND type = 1 AND event = 1 AND (parent IS NULL OR parent = 0)
             ORDER BY id",
            ARRAY_A
        );
        $this->checkError();
        return array_map(fn(array $row): array => [
            'id' => (int) $row['id'],
            'options' => $this->object($row['options']),
        ], $rows ?? []);
    }

    /**
     * The template for a scheduled timed reminder: its published translation in the
     * client's locale, else the reminder itself while it is still published.
     *
     * @return array{id: int, subject: string, body: mixed}|null
     */
    public function timedReminderTemplate(int $reminderId, string $locale): ?array
    {
        $row = $this->db->get_row($this->db->prepare(
            "SELECT id, subject, options FROM {$this->prefix()}reminders
             WHERE published = 1 AND type = 1 AND event = 1
               AND (id = %d OR (parent = %d AND lang = %s AND lang <> ''))
             ORDER BY (id = %d) ASC, id ASC LIMIT 1",
            $reminderId,
            $reminderId,
            $locale,
            $reminderId
        ), ARRAY_A);
        $this->checkError();
        if (!$row) {
            return null;
        }
        $options = $this->object($row['options']);
        return [
            'id' => (int) $row['id'],
            'subject' => (string) ($row['subject'] ?? ''),
            'body' => $options['body'] ?? null,
        ];
    }

    /** Legacy `AppointmentReminderListener` never schedules reminders for test appointments. */
    public function isTestClient(int $clientId): bool
    {
        $options = $this->db->get_var($this->db->prepare(
            "SELECT options FROM {$this->prefix()}clients WHERE id = %d",
            $clientId
        ));
        $this->checkError();
        return !empty($this->object(is_string($options) ? $options : null)['test_appointment']);
    }

    /** Everything a booking notification needs, read at delivery time. */
    public function appointmentContext(int $appointmentId): ?array
    {
        $row = $this->db->get_row($this->db->prepare(
            "SELECT a.id, a.status, a.start_at, a.type, a.edit_key, a.staff_id, a.location_id, a.options,
                    c.name AS client_name, c.email AS client_email, c.options AS client_options,
                    s.name AS service_name, l.name AS location_name, l.options AS location_options,
                    cal.name AS calendar_name, cal.wp_uid AS calendar_wp_uid, cal.options AS calendar_options
             FROM {$this->prefix()}appointments a
             LEFT JOIN {$this->prefix()}clients c ON c.id = a.client_id
             LEFT JOIN {$this->prefix()}services s ON s.id = a.service_id
             LEFT JOIN {$this->prefix()}locations l ON l.id = a.location_id
             LEFT JOIN {$this->prefix()}calendars cal ON cal.id = a.staff_id
             WHERE a.id = %d",
            $appointmentId
        ), ARRAY_A);
        $this->checkError();
        if (!$row) {
            return null;
        }
        $options = $this->object($row['options']);
        $clientOptions = $this->object($row['client_options']);
        $calendarOptions = $this->object($row['calendar_options']);
        $locationOptions = $this->object($row['location_options']);
        $phone = $options['booking_contact']['phone'] ?? ($clientOptions['phone'] ?? '');
        $locationId = (int) ($row['location_id'] ?? 0);
        return [
            'id' => (int) $row['id'],
            'status' => (int) $row['status'],
            'start' => (new \DateTimeImmutable((string) $row['start_at'], new \DateTimeZone('UTC')))->getTimestamp(),
            'type' => (int) $row['type'],
            'edit_key' => (string) $row['edit_key'],
            'staff_id' => (int) ($row['staff_id'] ?? 0),
            'client_name' => (string) ($row['client_name'] ?? ''),
            'client_email' => (string) ($row['client_email'] ?? ''),
            'client_phone' => is_string($phone) ? $phone : '',
            'client_timezone' => is_string($clientOptions['tz'] ?? null) ? $clientOptions['tz'] : '',
            'client_locale' => is_string($clientOptions['locale'] ?? null) ? $clientOptions['locale'] : '',
            'client_rtl' => !empty($clientOptions['rtl']),
            'test_appointment' => !empty($clientOptions['test_appointment']),
            'service_name' => (string) ($row['service_name'] ?? ''),
            // Legacy `getLocation()`/`getServiceAddress()`: only a linked location row has a name or address.
            'location_name' => $locationId > 0 ? (string) ($row['location_name'] ?? '') : '',
            'location_address' => $locationId > 0 && is_string($locationOptions['address'] ?? null) ? $locationOptions['address'] : '',
            'calendar_name' => (string) ($row['calendar_name'] ?? ''),
            'calendar_wp_uid' => (int) ($row['calendar_wp_uid'] ?? 0),
            'calendar_timezone' => is_string($calendarOptions['timezone'] ?? null) ? $calendarOptions['timezone'] : '',
        ];
    }

    private function object(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function checkError(): void
    {
        if ($this->db->last_error !== '') {
            error_log('Wappointment: notification lookup failed: ' . $this->db->last_error);
            throw new \RuntimeException('Notification data could not be read.');
        }
    }
}
