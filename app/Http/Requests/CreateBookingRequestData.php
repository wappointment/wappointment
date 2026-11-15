<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Email;
use Wappointment\Http\Attributes\Length;
use Wappointment\Http\Attributes\Min;
use Wappointment\System\Optional;

class CreateBookingRequestData extends BookingRequestData
{
    public function __construct(
        int $service_id,
        int $calendar_id,
        int $location_id,
        int $duration,
        string $date,
        #[Min(1)] public readonly int $start,
        #[Length(1, 100)] public readonly string $name,
        #[Email, Length(max: 191)] public readonly string|Optional $email = Optional::Missing,
        #[Length(max: 40)] public readonly string|Optional $phone = Optional::Missing,
        public readonly string|Optional $timezone = Optional::Missing,
    ) {
        parent::__construct($service_id, $calendar_id, $location_id, $duration, $date);
    }
}
