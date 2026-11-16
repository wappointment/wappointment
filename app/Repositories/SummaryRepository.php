<?php
declare(strict_types=1);

namespace Wappointment\Repositories;

/**
 * Reads for organizer summaries over the existing tables, plus the record of the last
 * period sent per calendar (WordPress option `wappointment_v3_summaries_sent`), which
 * stops a changed send time from producing a second summary for a period.
 */
class SummaryRepository
{
    public const SENT_OPTION = 'wappointment_v3_summaries_sent';

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
     * Calendars that get summaries: legacy `Calendars::all(true)` took active calendars
     * that offer at least one service. Legacy also required the precomputed
     * `availability` column, which v3 does not maintain, so it is not a condition here.
     *
     * @return list<array{id: int, name: string, wp_uid: int, timezone: string, options: array, duration: ?int}>
     */
    public function calendars(?int $calendarId = null): array
    {
        $where = $calendarId !== null ? $this->db->prepare(' AND c.id = %d', $calendarId) : '';
        $rows = $this->db->get_results(
            "SELECT c.id, c.name, c.wp_uid, c.options FROM {$this->prefix()}calendars c
             WHERE c.deleted_at IS NULL AND c.status > 0
               AND EXISTS (SELECT 1 FROM {$this->prefix()}calendar_service cs WHERE cs.calendar_id = c.id)
               {$where}
             ORDER BY c.sorting, c.id",
            ARRAY_A
        );
        $this->checkError();
        $calendars = [];
        foreach ($rows ?? [] as $row) {
            $options = $this->object($row['options']);
            $calendars[] = [
                'id' => (int) $row['id'],
                'name' => (string) ($row['name'] ?? ''),
                'wp_uid' => (int) ($row['wp_uid'] ?? 0),
                'timezone' => is_string($options['timezone'] ?? null) ? $options['timezone'] : '',
                'options' => $options,
                'duration' => $this->firstServiceDuration((int) $row['id']),
            ];
        }
        return $calendars;
    }

    /**
     * Appointments that start in [from, to): pending and confirmed, as legacy
     * `Sections::setAppointments` (status >= awaiting confirmation).
     *
     * @return list<array{start: int, end: int, buffer: int, client_name: string, client_email: string, location_name: string}>
     */
    public function appointments(int $calendarId, int $from, int $to): array
    {
        $rows = $this->db->get_results($this->db->prepare(
            "SELECT a.start_at, a.end_at, a.options, a.location_id, c.name AS client_name, c.email AS client_email, l.name AS location_name
             FROM {$this->prefix()}appointments a
             LEFT JOIN {$this->prefix()}clients c ON c.id = a.client_id
             LEFT JOIN {$this->prefix()}locations l ON l.id = a.location_id
             WHERE a.staff_id = %d AND a.status >= 0 AND a.start_at >= %s AND a.start_at < %s
             ORDER BY a.start_at, a.end_at, a.id",
            $calendarId,
            gmdate('Y-m-d H:i:s', $from),
            gmdate('Y-m-d H:i:s', $to)
        ), ARRAY_A);
        $this->checkError();
        $utc = new \DateTimeZone('UTC');
        return array_map(function (array $row) use ($utc): array {
            $options = $this->object($row['options']);
            return [
                'start' => (new \DateTimeImmutable((string) $row['start_at'], $utc))->getTimestamp(),
                'end' => (new \DateTimeImmutable((string) $row['end_at'], $utc))->getTimestamp(),
                'buffer' => max(0, (int) ($options['buffer_time'] ?? 0)) * 60,
                'client_name' => (string) ($row['client_name'] ?? ''),
                'client_email' => (string) ($row['client_email'] ?? ''),
                // Legacy `getLocation()` shows only a linked location's name.
                'location_name' => (int) ($row['location_id'] ?? 0) > 0 ? (string) ($row['location_name'] ?? '') : '',
            ];
        }, $rows ?? []);
    }

    /** @return array<string, string> period start (Y-m-d) keyed by "<kind>:<calendar id>" */
    public function lastSent(): array
    {
        $sent = get_option(self::SENT_OPTION, []);
        return is_array($sent) ? array_filter($sent, 'is_string') : [];
    }

    public function markSent(string $kind, int $calendarId, string $periodStart): bool
    {
        $sent = $this->lastSent();
        $key = self::key($kind, $calendarId);
        if (($sent[$key] ?? '') >= $periodStart) {
            return true;
        }
        $sent[$key] = $periodStart;
        return update_option(self::SENT_OPTION, $sent, false);
    }

    public static function key(string $kind, int $calendarId): string
    {
        return $kind . ':' . $calendarId;
    }

    /**
     * The slot duration the summary counts in. Legacy used the single `service` setting;
     * v3 calendars offer services from the services table, so the first duration of the
     * calendar's first service is used. Null when none is stored.
     */
    private function firstServiceDuration(int $calendarId): ?int
    {
        $options = $this->db->get_var($this->db->prepare(
            "SELECT s.options FROM {$this->prefix()}services s
             JOIN {$this->prefix()}calendar_service cs ON cs.service_id = s.id
             WHERE cs.calendar_id = %d AND s.deleted_at IS NULL
             ORDER BY s.sorting, s.id LIMIT 1",
            $calendarId
        ));
        $this->checkError();
        $durations = $this->object(is_string($options) ? $options : null)['durations'] ?? null;
        $duration = is_array($durations) && is_array($durations[0] ?? null) ? ($durations[0]['duration'] ?? null) : null;
        return is_numeric($duration) && (int) $duration >= 5 ? (int) $duration : null;
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
            error_log('Wappointment: summary lookup failed: ' . $this->db->last_error);
            throw new \RuntimeException('Summary data could not be read.');
        }
    }
}
