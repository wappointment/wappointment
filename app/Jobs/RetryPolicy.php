<?php
declare(strict_types=1);

namespace Wappointment\Jobs;

/**
 * Bounded retries matching legacy limits (3 attempts, 120 s delay), with the delay
 * growing per attempt. Attempts are counted when a job is claimed.
 */
final class RetryPolicy
{
    public const MAX_ATTEMPTS = 3;
    public const BACKOFF_SECONDS = 120;
    /** A reservation older than this belongs to a worker that stopped. */
    public const RESERVATION_TIMEOUT = 300;

    /** @return int|null the next available_at, or null when the job has exhausted its attempts. */
    public static function nextAttemptAt(int $attempts, int $now): ?int
    {
        if ($attempts >= self::MAX_ATTEMPTS) {
            return null;
        }
        return $now + self::BACKOFF_SECONDS * max(1, $attempts);
    }

    public static function isTimedOut(int $reservedAt, int $now): bool
    {
        return $reservedAt > 0 && $reservedAt < $now - self::RESERVATION_TIMEOUT;
    }
}
