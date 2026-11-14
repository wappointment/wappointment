<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\TimeRanges;
use Wappointment\System\Data;
use Wappointment\System\Optional;

class DefaultAvailabilityData extends Data
{
    public function __construct(
        #[TimeRanges] public readonly array|Optional $monday = Optional::Missing,
        #[TimeRanges] public readonly array|Optional $tuesday = Optional::Missing,
        #[TimeRanges] public readonly array|Optional $wednesday = Optional::Missing,
        #[TimeRanges] public readonly array|Optional $thursday = Optional::Missing,
        #[TimeRanges] public readonly array|Optional $friday = Optional::Missing,
        #[TimeRanges] public readonly array|Optional $saturday = Optional::Missing,
        #[TimeRanges] public readonly array|Optional $sunday = Optional::Missing,
        public readonly bool|Optional $precise = Optional::Missing,
    ) {}
}
