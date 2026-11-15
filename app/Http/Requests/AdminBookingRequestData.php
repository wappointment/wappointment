<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Min;

class AdminBookingRequestData extends BookingRequestData
{
    public function __construct(
        int $service_id,
        int $calendar_id,
        int $location_id,
        int $duration,
        string $date,
        #[Min(1)] public readonly int $start,
        #[Min(1)] public readonly int $client_id,
    ) {
        parent::__construct($service_id, $calendar_id, $location_id, $duration, $date);
    }
}
