<?php
declare(strict_types=1);

namespace Wappointment\Repositories;

use Wappointment\Jobs\QueuedJob;

/**
 * Durable queue over the unchanged `wappo_jobs` / `wappo_failed_jobs` tables.
 *
 * Column semantics are the legacy ones: Unix-second integers in `reserved_at`
 * (0 = free) and `available_at` (0 = now), `attempts` counted per run, and rows
 * deleted once their effect has happened. v3 counts an attempt when it claims a job.
 */
class JobQueueRepository
{
    private \wpdb $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    private function table(string $name = 'jobs'): string
    {
        return $this->db->prefix . 'wappo_' . $name;
    }

    /**
     * Insert a job. Call inside the transaction that records the domain change so the
     * job exists if and only if the change was committed.
     */
    public function enqueue(string $queue, string $payload, ?int $appointmentId, int $availableAt, int $now): int
    {
        $row = [
            'queue' => $queue,
            'payload' => $payload,
            'attempts' => 0,
            'reserved_at' => 0,
            'available_at' => $availableAt,
            'created_at' => $now,
        ];
        if ($appointmentId !== null) {
            $row['appointment_id'] = $appointmentId;
        }
        if ($this->db->insert($this->table(), $row) === false) {
            $this->failure();
        }
        return (int) $this->db->insert_id;
    }

    /**
     * Run $operation only if no other worker holds the queue lock. Returns false when
     * another worker is active.
     */
    public function withWorkerLock(callable $operation): bool
    {
        $name = 'wappo_queue_' . substr(hash('sha256', DB_NAME . $this->db->prefix), 0, 40);
        if ((string) $this->db->get_var($this->db->prepare('SELECT GET_LOCK(%s, 0)', $name)) !== '1') {
            return false;
        }
        try {
            $operation();
            return true;
        } finally {
            if ((string) $this->db->get_var($this->db->prepare('SELECT RELEASE_LOCK(%s)', $name)) !== '1') {
                error_log('Wappointment: could not release the job queue lock.');
            }
        }
    }

    /** @param list<string> $queues */
    public function available(array $queues, int $now, int $limit): array
    {
        return $this->rows($this->db->prepare(
            "SELECT * FROM {$this->table()}
             WHERE queue IN ({$this->placeholders($queues)}) AND reserved_at = 0 AND available_at <= %d
             ORDER BY available_at, id LIMIT %d",
            ...[...$queues, $now, $limit]
        ));
    }

    /**
     * Reserve a job for this worker. Only one claimant can succeed for a given row,
     * even if two workers somehow bypassed the worker lock.
     */
    public function claim(int $id, int $now): bool
    {
        $updated = $this->db->query($this->db->prepare(
            "UPDATE {$this->table()} SET reserved_at = %d, attempts = attempts + 1
             WHERE id = %d AND reserved_at = 0 AND available_at <= %d",
            $now, $id, $now
        ));
        if ($updated === false) {
            $this->failure();
        }
        return $updated === 1;
    }

    public function find(int $id): ?array
    {
        return $this->rows($this->db->prepare("SELECT * FROM {$this->table()} WHERE id = %d", $id))[0] ?? null;
    }

    public function complete(int $id): void
    {
        if ($this->db->delete($this->table(), ['id' => $id]) === false) {
            $this->failure();
        }
    }

    public function release(int $id, int $availableAt): void
    {
        if ($this->db->update($this->table(), ['reserved_at' => 0, 'available_at' => $availableAt], ['id' => $id]) === false) {
            $this->failure();
        }
    }

    /**
     * Move a job to `wappo_failed_jobs` with its original payload, then remove it from
     * the queue. The failure record is written first so a crash cannot lose the job.
     */
    public function fail(array $row, array $exception, int $now): void
    {
        if ($this->db->query('START TRANSACTION') === false) {
            $this->failure();
        }
        try {
            $failed = [
                'queue' => (string) $row['queue'],
                'payload' => (string) $row['payload'],
                'exception' => wp_json_encode($exception, JSON_UNESCAPED_SLASHES),
                'failed_at' => gmdate('Y-m-d H:i:s', $now),
            ];
            if (!empty($row['appointment_id'])) {
                $failed['appointment_id'] = (int) $row['appointment_id'];
            }
            if ($this->db->insert($this->table('failed_jobs'), $failed) === false) {
                $this->failure();
            }
            if ($this->db->delete($this->table(), ['id' => (int) $row['id']]) === false) {
                $this->failure();
            }
            if ($this->db->query('COMMIT') === false) {
                $this->failure();
            }
        } catch (\Throwable $error) {
            if ($this->db->query('ROLLBACK') === false) {
                error_log('Wappointment: job failure rollback failed: ' . $this->db->last_error);
            }
            throw $error;
        }
    }

    /** @param list<string> $queues */
    public function timedOut(array $queues, int $reservedBefore, int $limit): array
    {
        return $this->rows($this->db->prepare(
            "SELECT * FROM {$this->table()}
             WHERE queue IN ({$this->placeholders($queues)}) AND reserved_at > 0 AND reserved_at < %d
             ORDER BY reserved_at, id LIMIT %d",
            ...[...$queues, $reservedBefore, $limit]
        ));
    }

    /**
     * Release a timed-out reservation, but only if it still holds the reservation that
     * was observed as stale.
     */
    public function releaseTimedOut(int $id, int $staleReservation, int $availableAt): bool
    {
        $updated = $this->db->query($this->db->prepare(
            "UPDATE {$this->table()} SET reserved_at = 0, available_at = %d WHERE id = %d AND reserved_at = %d",
            $availableAt, $id, $staleReservation
        ));
        if ($updated === false) {
            $this->failure();
        }
        return $updated === 1;
    }

    /**
     * Pending rows in queues v3 does not consume, grouped by queue. These are legacy jobs
     * kept for the cutover report; v3 never deletes or executes them.
     *
     * @param list<string> $v3Queues
     * @return array<string, int>
     */
    public function pendingOutside(array $v3Queues): array
    {
        $rows = $this->rows($this->db->prepare(
            "SELECT queue, COUNT(*) AS total FROM {$this->table()}
             WHERE queue IS NULL OR queue NOT IN ({$this->placeholders($v3Queues)})
             GROUP BY queue ORDER BY queue",
            ...$v3Queues
        ));
        $counts = [];
        foreach ($rows as $row) {
            $counts[(string) ($row['queue'] ?? '')] = (int) $row['total'];
        }
        return $counts;
    }

    /**
     * Jobs on a v3 queue for one appointment that no worker has claimed yet.
     *
     * @return list<array<string, mixed>>
     */
    public function unclaimedForAppointment(string $queue, int $appointmentId): array
    {
        return $this->rows($this->db->prepare(
            "SELECT * FROM {$this->table()} WHERE queue = %s AND appointment_id = %d AND reserved_at = 0 ORDER BY id",
            $queue,
            $appointmentId
        ));
    }

    /**
     * Every job on a v3 queue that no worker has claimed yet.
     *
     * @return list<array<string, mixed>>
     */
    public function unclaimed(string $queue): array
    {
        return $this->rows($this->db->prepare(
            "SELECT * FROM {$this->table()} WHERE queue = %s AND reserved_at = 0 ORDER BY id",
            $queue
        ));
    }

    /** Delete a job only while it is still unclaimed, so a running delivery is never pulled from under a worker. */
    public function deleteUnclaimed(int $id): bool
    {
        $deleted = $this->db->query($this->db->prepare(
            "DELETE FROM {$this->table()} WHERE id = %d AND reserved_at = 0",
            $id
        ));
        if ($deleted === false) {
            $this->failure();
        }
        return $deleted === 1;
    }

    private function placeholders(array $values): string
    {
        if ($values === []) {
            throw new \InvalidArgumentException('At least one queue is required.');
        }
        return implode(', ', array_fill(0, count($values), '%s'));
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
        error_log('Wappointment: job queue database error: ' . $this->db->last_error);
        throw new \RuntimeException('The job queue could not be updated.');
    }
}
