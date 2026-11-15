<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\In;
use Wappointment\Http\Attributes\Min;
use Wappointment\System\Data;

class OwnCalendarStatusData extends Data
{
    public function __construct(
        #[Min(1)] public readonly int $id,
        #[In([0, 1])] public readonly int $type,
        public readonly int $start,
        public readonly int $end,
    ) {}
}
