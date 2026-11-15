<?php
declare(strict_types=1);

namespace Wappointment\Booking;

class Availability
{
    public function slots(
        string $date,
        array $calendar,
        int $duration,
        int $buffer,
        array $settings,
        array $busy,
        array $extra,
        int $now
    ): array {
        $timezone = new \DateTimeZone($calendar['options']['timezone'] ?? 'UTC');
        $day = \DateTimeImmutable::createFromFormat('!Y-m-d', $date, $timezone);
        if (!$day || $day->format('Y-m-d') !== $date) {
            throw new BookingException(__('Choose a valid date.', 'wappointment'));
        }
        if ($duration < 5 || $duration > 1440 || $buffer < 0 || $buffer > 1440) {
            throw new BookingException(__('Invalid appointment duration or buffer.', 'wappointment'));
        }
        $customStep = !empty($settings['more_st']);
        $step = $customStep ? (int) ($settings['starting_each'] ?? 30) : $duration + $buffer;
        if ($step < 1 || ($customStep && $step > 1440)) {
            throw new BookingException(__('Invalid slot interval. Review booking settings.', 'wappointment'), 409);
        }
        $noticeHours = $settings['hours_before_booking_allowed'] ?? 3;
        if (!is_numeric($noticeHours) || !is_finite((float) $noticeHours) || $noticeHours < 0) {
            throw new BookingException(__('Invalid minimum booking notice. Review booking settings.', 'wappointment'), 409);
        }
        $wholeNoticeHours = (int) floor((float) $noticeHours);
        $noticeMinutes = (int) floor(((float) $noticeHours - $wholeNoticeHours) * 60);
        $earliest = $now + ($wholeNoticeHours * 3600) + ($noticeMinutes * 60);
        $nowLocal = (new \DateTimeImmutable('@' . $now))->setTimezone($timezone);
        $bookingDaysValue = $calendar['options']['avb'] ?? 60;
        if (!is_numeric($bookingDaysValue) || !is_finite((float) $bookingDaysValue)
            || (float) $bookingDaysValue !== (float) (int) $bookingDaysValue) {
            throw new BookingException(__('Invalid booking horizon. Review your calendar settings.', 'wappointment'), 409);
        }
        $bookingDays = (int) $bookingDaysValue;
        if ($bookingDays < 1 || $bookingDays > 365) {
            throw new BookingException(__('Invalid booking horizon. Review your calendar settings.', 'wappointment'), 409);
        }
        $refreshAt = $settings['refreshavb_at'] ?? 23;
        if (!is_numeric($refreshAt) || !is_finite((float) $refreshAt) || $refreshAt < 0 || $refreshAt > 23) {
            throw new BookingException(__('Invalid availability refresh time. Review booking settings.', 'wappointment'), 409);
        }
        $refreshEnabled = $settings['allow_refreshavb'] ?? false;
        if (!in_array($refreshEnabled, [true, false, 0, 1, '0', '1'], true)) {
            throw new BookingException(__('Invalid availability refresh setting. Review booking settings.', 'wappointment'), 409);
        }
        $effectiveDays = $bookingDays;
        if (!empty($refreshEnabled) && (float) $nowLocal->format('G') < (float) $refreshAt) {
            $effectiveDays--;
        }
        $latestDate = $nowLocal->modify('+' . $effectiveDays . ' days')->format('Y-m-d');
        if ($date < $nowLocal->format('Y-m-d') || $date > $latestDate) {
            return [];
        }
        $isToday = $date === $nowLocal->format('Y-m-d');
        $fluidStart = !empty($settings['availability_fluid']) && $isToday
            ? $this->roundUpToFiveMinutes($earliest, $timezone)
            : null;
        $windows = $extra;
        $weekday = strtolower($day->format('l'));
        try {
            $schedule = WeeklySchedule::minutes(array_key_exists('regav', $calendar['options']) ? $calendar['options']['regav'] : []);
        } catch (\InvalidArgumentException $error) {
            throw new BookingException(__('Invalid weekly availability or availability conditions. Review your calendar and its stored precision before accepting bookings.', 'wappointment'), 409);
        }
        foreach ($schedule[$weekday] as $block) {
            $windows[] = [
                $this->minute($day, (int) $block[0]),
                $this->minute($day, (int) $block[1]),
            ];
        }
        $slots = [];
        $dayEnd = $day->modify('+1 day')->getTimestamp();
        $length = ($duration + $buffer) * 60;
        usort($windows, static fn(array $left, array $right): int => $left[0] <=> $right[0]);
        $merged = [];
        foreach ($windows as [$from, $to]) {
            $last = count($merged) - 1;
            if ($last >= 0 && $from <= $merged[$last][1]) {
                $merged[$last][1] = max($merged[$last][1], $to);
            } else {
                $merged[] = [$from, $to];
            }
        }
        foreach ($busy as [$busyStart, $busyEnd]) {
            $remaining = [];
            foreach ($merged as [$from, $to]) {
                if ($busyStart >= $to || $busyEnd <= $from) {
                    $remaining[] = [$from, $to];
                    continue;
                }
                if ($busyStart > $from) {
                    $remaining[] = [$from, $busyStart];
                }
                if ($busyEnd < $to) {
                    $remaining[] = [$busyEnd, $to];
                }
            }
            $merged = $remaining;
        }
        foreach ($merged as [$from, $to]) {
            $from = max($from, $day->getTimestamp());
            $to = min($to, $dayEnd);
            if ($fluidStart !== null) {
                $from = max($from, $fluidStart);
            } elseif ($from < $earliest) {
                $initialStep = !empty($settings['more_st']) ? $step : $duration;
                $increments = (int) ceil(($earliest - $from) / ($initialStep * 60));
                $from += $increments * $initialStep * 60;
            }
            for ($start = $from; $start + $length <= $to; $start += $step * 60) {
                $end = $start + $length;
                $slots[$start] = [
                    'start' => $start,
                    'end' => $start + $duration * 60,
                    'reserved_until' => $end,
                ];
            }
        }
        ksort($slots, SORT_NUMERIC);
        return array_values($slots);
    }

    private function minute(\DateTimeImmutable $day, int $minute): int
    {
        return $minute === 1440
            ? $day->modify('+1 day')->getTimestamp()
            : $day->setTime(intdiv($minute, 60), $minute % 60)->getTimestamp();
    }

    private function roundUpToFiveMinutes(int $timestamp, \DateTimeZone $timezone): int
    {
        $time = (new \DateTimeImmutable('@' . $timestamp))->setTimezone($timezone);
        $minute = (int) $time->format('i') + ((int) $time->format('s') > 0 ? 1 : 0);
        $rounded = (int) (ceil($minute / 5) * 5);
        return $time->setTime((int) $time->format('G'), $rounded)->getTimestamp();
    }
}
