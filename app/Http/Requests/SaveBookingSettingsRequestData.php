<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\In;
use Wappointment\Http\Attributes\Length;
use Wappointment\Http\Attributes\Max;
use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Data\WeeklyAvailabilityData;
use Wappointment\System\Optional;

class SaveBookingSettingsRequestData extends RequestData
{
    public function __construct(
        #[Length(1, 255)] public readonly string $service_name,
        #[Length(1, 255)] public readonly string $calendar_name,
        #[Length(1, 255)] public readonly string $location_name,
        #[In([1, 2, 4])] public readonly int $location_type,
        #[Min(5), Max(1440)] public readonly int $duration,
        #[Min(1), Max(365)] public readonly int $booking_days,
        #[Length(1, 255)] public readonly string $timezone,
        public readonly bool $phone_required,
        public readonly WeeklyAvailabilityData $regav,
        #[Min(0)] public readonly int|Optional $booking_page = Optional::Missing,
    ) {}
}
