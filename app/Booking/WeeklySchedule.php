<?php
declare(strict_types=1);

namespace Wappointment\Booking;

class WeeklySchedule
{
    public static function minutes(mixed $stored): array
    {
        $days = array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], []);
        if (!is_array($stored) || array_diff(array_keys($stored), [...array_keys($days), 'precise'])
            || !in_array($stored['precise'] ?? null, [null, false, true, 0, 1], true)) {
            throw new \InvalidArgumentException('Unsupported weekly availability format.');
        }
        $precise = !empty($stored['precise']);
        foreach ($days as $day => $_) {
            $ranges = array_key_exists($day, $stored) ? $stored[$day] : [];
            if (!is_array($ranges) || !array_is_list($ranges) || count($ranges) > 24) {
                throw new \InvalidArgumentException('Unsupported weekly availability ranges.');
            }
            foreach ($ranges as $range) {
                if (!is_array($range) || !array_is_list($range) || count($range) !== 2) {
                    throw new \InvalidArgumentException('Unsupported weekly availability or availability conditions.');
                }
                foreach ($range as $value) {
                    if (!is_numeric($value) || !is_finite((float) $value) || $value < 0 || $value > ($precise ? 1440 : 24)) {
                        throw new \InvalidArgumentException('Weekly availability values do not match the stored precision.');
                    }
                }
                // Master passes values to Carbon addHours/addMinutes, which truncate fractions.
                $start = (int) $range[0] * ($precise ? 1 : 60);
                $end = (int) $range[1] * ($precise ? 1 : 60);
                if ($start >= $end) {
                    throw new \InvalidArgumentException('Weekly availability ranges must increase.');
                }
                $days[$day][] = [$start, $end];
            }
        }
        return $days;
    }
}
