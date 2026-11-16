<?php
declare(strict_types=1);

namespace Wappointment\Jobs;

/**
 * The numbers in an organizer summary, using the legacy master formulas from
 * `Services\Sections`: free slots are whole slot durations that fit in each free window,
 * and coverage is booked time (appointment rows including their buffer) over booked plus
 * free time. When that total is shorter than one slot there is no coverage and the email
 * says there were no availabilities.
 */
final class SummaryFigures
{
    /** @param list<array{int, int}> $freeWindows */
    public static function freeSlots(array $freeWindows, int $durationSeconds): int
    {
        if ($durationSeconds < 1) {
            throw new \InvalidArgumentException('A slot duration is required.');
        }
        $slots = 0;
        foreach ($freeWindows as [$from, $to]) {
            $slots += intdiv(max(0, $to - $from), $durationSeconds);
        }
        return $slots;
    }

    /**
     * @param list<array{int, int}> $booked appointment start/end including the buffer
     * @param list<array{int, int}> $freeWindows
     * @return int|null the rounded percentage, or null when there was no availability
     */
    public static function coverage(array $booked, array $freeWindows, int $durationSeconds): ?int
    {
        $bookedTime = 0;
        foreach ($booked as [$from, $to]) {
            $bookedTime += $to - $from;
        }
        $freeTime = 0;
        foreach ($freeWindows as [$from, $to]) {
            $freeTime += $to - $from;
        }
        $total = $bookedTime + $freeTime;
        return $total < $durationSeconds || $total <= 0 ? null : (int) round($bookedTime / $total * 100);
    }
}
