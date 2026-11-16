<?php
declare(strict_types=1);

namespace Wappointment\Jobs;

/**
 * When a timed reminder is sent, following legacy master `Models\Reminder::getDelay()`
 * and `Listeners\IsReminder`: `options.when_number` units of `options.when_unit`
 * (1 minutes, 2 hours, 3 days) before the appointment starts, using fixed-length units.
 * A reminder whose time has already passed when the booking is confirmed is not sent.
 *
 * Legacy treated an unknown unit or a zero/missing number as "no delay" and sent the
 * reminder immediately; v3 does not schedule such a reminder at all.
 */
final class ReminderSchedule
{
    private const UNIT_SECONDS = [1 => 60, 2 => 3600, 3 => 86400];

    /** @return int|null the Unix time to send, or null when the reminder should not be scheduled */
    public static function sendAt(array $options, int $start, int $now): ?int
    {
        $unit = self::positiveInt($options['when_unit'] ?? null);
        $number = self::positiveInt($options['when_number'] ?? null);
        if ($unit === null || $number === null || !isset(self::UNIT_SECONDS[$unit])) {
            return null;
        }
        $sendAt = $start - $number * self::UNIT_SECONDS[$unit];
        return $sendAt < $now ? null : $sendAt;
    }

    private static function positiveInt(mixed $value): ?int
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        $int = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $int === false ? null : $int;
    }
}
