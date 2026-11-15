<?php
declare(strict_types=1);

namespace Wappointment\Booking;

final class RecurringStatusOccurrences
{
    public function expand(array $status, int $from, int $to): array
    {
        if ($to <= $from) {
            throw $this->invalid();
        }

        $recur = $this->integer($status['recur'] ?? null);
        $options = $status['options'] ?? null;
        if ($recur < 1 || $recur > 4 || !is_array($options)) {
            throw $this->invalid();
        }
        $timezoneName = $options['origin_tz'] ?? null;
        $originStart = $options['origin_start'] ?? null;
        if (!is_string($timezoneName) || !is_string($originStart)) {
            throw $this->invalid();
        }

        try {
            $timezone = new \DateTimeZone($timezoneName);
            $anchor = $this->dateTime($originStart, $timezone);
            $start = $this->timestamp($status['start_at'] ?? null);
            $end = $this->timestamp($status['end_at'] ?? null);
        } catch (\Exception $error) {
            throw $this->invalid();
        }
        $duration = $end - $start;
        if ($duration <= 0) {
            throw $this->invalid();
        }

        $interval = empty($options['interval']) ? 1 : $this->integer($options['interval']);
        if ($interval < 1 || $interval > 1000000) {
            throw $this->invalid();
        }
        $until = empty($options['until']) ? null : $this->integer($options['until']);
        $excluded = $this->excludedStarts($options['exdate'] ?? []);
        if ($recur === 3) {
            return $this->expandMonthly(
                $anchor,
                $this->monthDay($options, $anchor) ?? (int) $anchor->format('j'),
                $interval,
                $start,
                $duration,
                $from,
                $to,
                $until,
                $excluded
            );
        }
        $weekdays = $recur === 2 ? $this->weekdays($options['byday'] ?? [$anchor->format('w')]) : [];
        $firstPeriod = $this->firstPeriod($recur, $anchor, $from - $duration, $interval);
        $occurrences = [];
        $period = $firstPeriod;

        for ($count = 0; $count < 500; $count++) {
            $candidates = $this->periodStarts($recur, $anchor, $period, $interval, $weekdays);
            foreach ($candidates as $localStart) {
                $occurrenceStart = $localStart->getTimestamp();
                if ($occurrenceStart < $start || $occurrenceStart >= $to || ($until !== null && $occurrenceStart > $until)) {
                    continue;
                }
                $occurrenceEnd = $occurrenceStart + $duration;
                if ($occurrenceEnd > $from && !in_array($occurrenceStart, $excluded, true)) {
                    $occurrences[] = [$occurrenceStart, $occurrenceEnd];
                }
            }

            if ($this->periodBoundary($recur, $anchor, $period + 1, $interval)->getTimestamp() >= $to) {
                return $occurrences;
            }
            $period++;
        }

        throw $this->invalid();
    }

    private function periodStarts(int $recur, \DateTimeImmutable $anchor, int $period, int $interval, array $weekdays): array
    {
        return match ($recur) {
            1 => [$anchor->modify('+' . ($period * $interval) . ' days')],
            2 => $this->weeklyStarts($anchor, $period * $interval, $weekdays),
            3 => throw new \LogicException('Monthly occurrences use their dedicated iterator.'),
            4 => [$this->yearlyStart($anchor, $period, $interval)],
        };
    }

    private function weeklyStarts(\DateTimeImmutable $anchor, int $weeks, array $weekdays): array
    {
        $monday = $anchor->modify('monday this week')->setTime(0, 0);
        $starts = [];
        foreach ($weekdays as $weekday) {
            $day = $monday->modify('+' . ($weekday === 0 ? 6 : $weekday - 1) . ' days');
            $starts[] = $day->setTime(
                (int) $anchor->format('G'),
                (int) $anchor->format('i'),
                (int) $anchor->format('s')
            )->modify('+' . $weeks . ' weeks');
        }
        return $starts;
    }

    private function periodBoundary(int $recur, \DateTimeImmutable $anchor, int $period, int $interval): \DateTimeImmutable
    {
        return match ($recur) {
            1 => $anchor->modify('+' . ($period * $interval) . ' days'),
            2 => $anchor->modify('monday this week')->setTime(0, 0)->modify('+' . ($period * $interval) . ' weeks'),
            3 => throw new \LogicException('Monthly occurrences use their dedicated iterator.'),
            4 => $anchor->setDate((int) $anchor->format('Y'), 1, 1)->setTime(0, 0)->modify('+' . ($period * $interval) . ' years'),
        };
    }

    private function expandMonthly(
        \DateTimeImmutable $anchor,
        int|array $monthDay,
        int $interval,
        int $originalStart,
        int $duration,
        int $from,
        int $to,
        ?int $until,
        array $excluded
    ): array {
        $occurrences = [];
        $candidate = $anchor;
        for ($count = 0; $count < 10000; $count++) {
            $occurrenceStart = $candidate->getTimestamp();
            if ($occurrenceStart >= $to || ($until !== null && $occurrenceStart > $until)) {
                return $occurrences;
            }
            $occurrenceEnd = $occurrenceStart + $duration;
            if ($occurrenceStart >= $originalStart && $occurrenceEnd > $from
                && !in_array($occurrenceStart, $excluded, true)) {
                $occurrences[] = [$occurrenceStart, $occurrenceEnd];
            }

            $candidate = $this->nextMonthly($candidate, $monthDay, $interval, $anchor);
        }

        throw $this->invalid();
    }

    private function nextMonthly(
        \DateTimeImmutable $current,
        int|array $monthDay,
        int $interval,
        \DateTimeImmutable $anchor
    ): \DateTimeImmutable {
        for ($count = 0; $count < 500; $count++) {
            $month = $current->modify('+' . $interval . ' months')->modify('first day of this month')->setTime(0, 0);
            $candidate = $this->monthlyDate($month, $monthDay, $anchor);
            if ($candidate !== null) {
                return $candidate;
            }
            $current = $month;
        }
        throw $this->invalid();
    }

    private function monthlyDate(\DateTimeImmutable $month, int|array $monthDay, \DateTimeImmutable $anchor): ?\DateTimeImmutable
    {
        if (is_array($monthDay)) {
            $weekday = $monthDay['day'];
            if ($monthDay['each'] === 'last') {
                $date = $month->modify('last day of this month');
                $difference = ((int) $date->format('w') - $weekday + 7) % 7;
                return $date->modify('-' . $difference . ' days')->setTime(
                    (int) $anchor->format('G'), (int) $anchor->format('i'), (int) $anchor->format('s')
                );
            }
            $firstWeekday = (int) $month->format('w');
            $difference = ($weekday - $firstWeekday + 7) % 7 + (($monthDay['each'] - 1) * 7);
            $date = $month->modify('+' . $difference . ' days');
            if ($date->format('n') !== $month->format('n')) {
                return null;
            }
            return $date->setTime(
                (int) $anchor->format('G'), (int) $anchor->format('i'), (int) $anchor->format('s')
            );
        }

        return $month->setDate((int) $month->format('Y'), (int) $month->format('n'), $monthDay)->setTime(
            (int) $anchor->format('G'), (int) $anchor->format('i'), (int) $anchor->format('s')
        );
    }

    private function yearlyStart(\DateTimeImmutable $anchor, int $period, int $interval): \DateTimeImmutable
    {
        for ($count = 0; $count < $period; $count++) {
            $anchor = $anchor->modify('+' . $interval . ' years');
        }
        return $anchor;
    }

    private function firstPeriod(int $recur, \DateTimeImmutable $anchor, int $windowStart, int $interval): int
    {
        $localWindow = (new \DateTimeImmutable('@' . $windowStart))->setTimezone($anchor->getTimezone());
        if ($localWindow <= $anchor) {
            return 0;
        }

        if ($recur === 1) {
            $days = (int) $anchor->setTime(0, 0)->diff($localWindow->setTime(0, 0))->format('%a');
            return max(0, intdiv($days, $interval) - 1);
        }
        if ($recur === 2) {
            $anchorWeek = $anchor->modify('monday this week')->setTime(0, 0);
            $windowWeek = $localWindow->modify('monday this week')->setTime(0, 0);
            $days = (int) $anchorWeek->diff($windowWeek)->format('%a');
            return max(0, intdiv($days, 7 * $interval) - 1);
        }
        $years = (int) $localWindow->format('Y') - (int) $anchor->format('Y');
        return max(0, intdiv($years, $interval) - 1);
    }

    private function monthDay(array $options, \DateTimeImmutable $anchor): int|array|null
    {
        if (!empty($options['bymonthday'])) {
            $day = $this->integer($options['bymonthday']);
            if ($day < 1 || $day > 31) {
                throw $this->invalid();
            }
            return $day;
        }
        if (!empty($options['byday'])) {
            $rule = $options['byday'];
            if (is_array($rule) && isset($rule['each'], $rule['day'])) {
                $weekday = $this->integer($rule['day']);
                $ordinal = $rule['each'];
                if ($weekday < 0 || $weekday > 6
                    || !(($ordinal === 'last') || (is_int($ordinal) && $ordinal >= 1 && $ordinal <= 5))) {
                    throw $this->invalid();
                }
                return ['each' => $ordinal, 'day' => $weekday];
            }
            if (is_array($rule) && isset($rule[0]) && is_array($rule[0])) {
                $rule = $rule[0];
                if (isset($rule['each'], $rule['day'])) {
                    $weekday = $this->integer($rule['day']);
                    $ordinal = $rule['each'];
                    if ($weekday >= 0 && $weekday <= 6
                        && (($ordinal === 'last') || (is_int($ordinal) && $ordinal >= 1 && $ordinal <= 5))) {
                        return ['each' => $ordinal, 'day' => $weekday];
                    }
                }
            }
            throw $this->invalid();
        }
        return null;
    }

    private function weekdays(mixed $value): array
    {
        if (!is_array($value) || !$value) {
            throw $this->invalid();
        }
        $days = [];
        foreach ($value as $weekday) {
            $weekday = $this->integer($weekday);
            if ($weekday < 0 || $weekday > 6) {
                throw $this->invalid();
            }
            $days[] = $weekday;
        }
        $days = array_values(array_unique($days));
        sort($days);
        return $days;
    }

    private function excludedStarts(mixed $value): array
    {
        if (!is_array($value)) {
            throw $this->invalid();
        }
        return array_map(fn(mixed $timestamp): int => $this->integer($timestamp), $value);
    }

    private function timestamp(mixed $value): int
    {
        if (!is_string($value) || $value === '') {
            throw $this->invalid();
        }
        return $this->dateTime($value, new \DateTimeZone('UTC'))->getTimestamp();
    }

    private function dateTime(string $value, \DateTimeZone $timezone): \DateTimeImmutable
    {
        foreach (['!Y-m-d H:i:s' => 'Y-m-d H:i:s', '!Y-m-d H:i' => 'Y-m-d H:i'] as $format => $expected) {
            $date = \DateTimeImmutable::createFromFormat($format, $value, $timezone);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($date && $date->format($expected) === $value
                && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return $date;
            }
        }
        throw $this->invalid();
    }

    private function integer(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && preg_match('/^-?[0-9]+$/D', $value)) {
            return (int) $value;
        }
        throw $this->invalid();
    }

    private function invalid(): BookingException
    {
        return new BookingException(__('Invalid recurring availability. Review the stored calendar status.', 'wappointment'), 409);
    }
}
