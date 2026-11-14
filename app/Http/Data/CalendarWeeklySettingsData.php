<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Length;
use Wappointment\Http\Attributes\Max;
use Wappointment\Http\Attributes\Min;
use Wappointment\System\Data;

class CalendarWeeklySettingsData extends Data
{
    public function __construct(
        #[Length(1, 255)] public readonly string $timezone,
        #[Min(1), Max(365)] public readonly int $booking_days,
        public readonly WeeklyAvailabilityData $regav,
    ) {}

    protected function validate(): void
    {
        if (!in_array($this->timezone, \DateTimeZone::listIdentifiers(), true) && $this->timezone !== 'UTC') {
            throw new \InvalidArgumentException('Choose a valid timezone.');
        }
        foreach ($this->regav->toArray() as $ranges) {
            foreach ($ranges as [$start, $end]) {
                if ($start >= $end) {
                    throw new \InvalidArgumentException('Availability ranges must have increasing start and end times.');
                }
            }
        }
    }
}
