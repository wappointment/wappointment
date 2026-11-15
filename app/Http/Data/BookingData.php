<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Attributes\Pattern;
use Wappointment\Http\Responses\ResponseData;

class BookingData extends ResponseData
{
    public function __construct(
        #[Min(1)] public readonly int $id,
        public readonly int $status,
        #[Min(1)] public readonly int $start,
        #[Min(1)] public readonly int $end,
        #[Pattern('^[a-f0-9]{32}$')] public readonly string $edit_key,
        #[Min(1)] public readonly int $service_id,
        #[Min(1)] public readonly int $calendar_id,
        #[Min(1)] public readonly int $location_id,
        #[Min(5)] public readonly int $duration,
    ) {}
}
