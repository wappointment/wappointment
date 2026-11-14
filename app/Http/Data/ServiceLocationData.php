<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Min;
use Wappointment\Http\Attributes\Pattern;
use Wappointment\System\Data;

class ServiceLocationData extends Data
{
    public function __construct(
        #[Min(1), Pattern('^[1-9][0-9]*$')] public readonly int|string $service_id,
        #[Min(1), Pattern('^[1-9][0-9]*$')] public readonly int|string $location_id,
    ) {}
}
