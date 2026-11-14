<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Date;
use Wappointment\Http\Attributes\Max;
use Wappointment\Http\Attributes\Min;

class BookingRequestData extends RequestData
{
    public function __construct(
        #[Min(1)] public readonly int $service_id,
        #[Min(1)] public readonly int $calendar_id,
        #[Min(1)] public readonly int $location_id,
        #[Min(5), Max(1440)] public readonly int $duration,
        #[Date] public readonly string $date,
    ) {}
}
