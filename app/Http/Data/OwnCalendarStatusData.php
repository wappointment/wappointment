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
        #[In([0, 1, 2, 3, 4])] public readonly int $recur = 0,
        public readonly int $interval = 1,
        /** @var list<int> */
        #[\Wappointment\Http\Attributes\CollectionOf('int')] public readonly array $weekdays = [],
        public readonly ?int $month_day = null,
        public readonly ?string $until = null,
        public readonly string $timezone = 'UTC',
        public readonly bool $editable = true,
    ) {}
}
