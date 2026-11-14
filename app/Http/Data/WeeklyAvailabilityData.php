<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\TimeRanges;
use Wappointment\System\Data;

class WeeklyAvailabilityData extends Data
{
    public function __construct(
        #[TimeRanges(max: 24)] public readonly array $monday,
        #[TimeRanges(max: 24)] public readonly array $tuesday,
        #[TimeRanges(max: 24)] public readonly array $wednesday,
        #[TimeRanges(max: 24)] public readonly array $thursday,
        #[TimeRanges(max: 24)] public readonly array $friday,
        #[TimeRanges(max: 24)] public readonly array $saturday,
        #[TimeRanges(max: 24)] public readonly array $sunday,
    ) {}
}
