<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Attributes\Date;
use Wappointment\Http\Attributes\In;
use Wappointment\Http\Attributes\Max;
use Wappointment\Http\Attributes\Min;

class UpdateOwnCalendarStatusRequestData extends RequestData
{
    public function __construct(
        #[Min(1)] public readonly int $id,
        #[Min(1)] public readonly int $status_id,
        #[In([0, 1])] public readonly int $type,
        #[Min(0)] public readonly int $start,
        #[Min(0)] public readonly int $end,
        #[In([0, 1, 2, 3, 4])] public readonly int $recur = 0,
        #[Min(1), Max(366)] public readonly int $interval = 1,
        #[CollectionOf('int')] public readonly array $weekdays = [],
        #[Min(1), Max(31)] public readonly ?int $month_day = null,
        #[Date] public readonly ?string $until = null,
        #[In(['self', 'admin'])] public readonly string $scope = 'self',
    ) {}
}
