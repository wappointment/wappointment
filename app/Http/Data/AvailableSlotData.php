<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Min;
use Wappointment\System\Data;

class AvailableSlotData extends Data
{
    public function __construct(
        #[Min(1)] public readonly int $start,
        #[Min(1)] public readonly int $end,
        #[Min(1)] public readonly int $reserved_until,
    ) {}
}
