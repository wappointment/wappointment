<?php
declare(strict_types=1);

namespace Wappointment\Jobs;

/**
 * When organizer summaries are sent and which days they cover, following legacy master
 * `Services\Queue::queueDailyJob/queueWeeklyJob` and `Messages\Admin*SummaryEmail`:
 *
 * - daily: every day at `daily_summary_time` (hour 0-23) in the calendar's timezone,
 *   covering the next local day;
 * - weekly: at `weekly_summary_time` on `weekly_summary_day` (0 Sunday - 6 Saturday),
 *   covering the seven local days that start the next day.
 *
 * A period is named by its first local date (Y-m-d). Legacy computed "tomorrow" when the
 * job ran, so a delayed job described the wrong day, and its weekly window stopped at the
 * start of the seventh day; v3 stores the period in the job and covers all seven days.
 */
final class SummarySchedule
{
    public const DAILY = 'daily';
    public const WEEKLY = 'weekly';
    public const KINDS = [self::DAILY, self::WEEKLY];

    public static function enabled(string $kind, array $settings): bool
    {
        return !empty($settings[$kind . '_summary']);
    }

    /**
     * The next period to schedule: the first one whose send time is not in the past and
     * that starts after the last period already sent for this calendar.
     *
     * @return array{period_start: string, send_at: int}|null null when the settings are invalid
     */
    public static function next(string $kind, array $settings, \DateTimeZone $zone, int $now, ?string $lastSent): ?array
    {
        $date = (new \DateTimeImmutable('@' . $now))->setTimezone($zone)->format('Y-m-d');
        $day = self::localDay($date, $zone);
        for ($offset = 0; $offset <= 8; $offset++) {
            $sendDate = $day->modify('+' . $offset . ' days')->format('Y-m-d');
            $period = self::localDay($sendDate, $zone)->modify('+1 day')->format('Y-m-d');
            $sendAt = self::sendAt($kind, $settings, $zone, $period);
            if ($sendAt !== null && $sendAt >= $now && ($lastSent === null || $period > $lastSent)) {
                return ['period_start' => $period, 'send_at' => $sendAt];
            }
        }
        return null;
    }

    /**
     * When the summary for a period is due under the current settings, or null when the
     * settings are invalid or (weekly) the period does not start the day after the
     * configured weekday. A DST gap moves a nonexistent hour forward, as PHP does.
     */
    public static function sendAt(string $kind, array $settings, \DateTimeZone $zone, string $periodStart): ?int
    {
        $hour = self::bounded($settings[$kind . '_summary_time'] ?? null, 0, 23);
        if ($hour === null || !self::validDate($periodStart)) {
            return null;
        }
        $sendDay = self::localDay($periodStart, $zone)->modify('-1 day');
        if ($kind === self::WEEKLY) {
            $weekday = self::bounded($settings['weekly_summary_day'] ?? null, 0, 6);
            if ($weekday === null || (int) $sendDay->format('w') !== $weekday) {
                return null;
            }
        } elseif ($kind !== self::DAILY) {
            return null;
        }
        return $sendDay->setTime($hour, 0)->getTimestamp();
    }

    /**
     * The local dates and the UTC bounds [from, to) of a period.
     *
     * @return array{dates: list<string>, from: int, to: int}
     */
    public static function period(string $kind, string $periodStart, \DateTimeZone $zone): array
    {
        $first = self::localDay($periodStart, $zone);
        $length = $kind === self::WEEKLY ? 7 : 1;
        $dates = [];
        for ($index = 0; $index < $length; $index++) {
            $dates[] = $first->modify('+' . $index . ' days')->format('Y-m-d');
        }
        return [
            'dates' => $dates,
            'from' => $first->getTimestamp(),
            'to' => $first->modify('+' . $length . ' days')->getTimestamp(),
        ];
    }

    public static function validDate(mixed $date): bool
    {
        if (!is_string($date)) {
            return false;
        }
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date, new \DateTimeZone('UTC'));
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    private static function localDay(string $date, \DateTimeZone $zone): \DateTimeImmutable
    {
        $day = \DateTimeImmutable::createFromFormat('!Y-m-d', $date, $zone);
        if ($day === false) {
            throw new \InvalidArgumentException('Invalid summary date "' . $date . '".');
        }
        return $day;
    }

    private static function bounded(mixed $value, int $min, int $max): ?int
    {
        if (is_float($value) && $value === floor($value)) {
            $value = (int) $value;
        }
        if (is_string($value)) {
            $value = trim($value);
        }
        $int = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
        return $int === false ? null : $int;
    }
}
