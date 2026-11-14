<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Min;
use Wappointment\System\Optional;

class BookingSlotData extends BookingRequestData
{
    public function __construct(
        int $service_id,
        int $calendar_id,
        int $location_id,
        int $duration,
        string $date,
        #[Min(1)] public readonly int|Optional $start = Optional::Missing,
    ) {
        parent::__construct($service_id, $calendar_id, $location_id, $duration, $date);
    }
}
