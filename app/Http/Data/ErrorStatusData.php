<?php
declare(strict_types=1);

namespace Wappointment\Http\Data;

use Wappointment\Http\Attributes\Max;
use Wappointment\Http\Attributes\Min;
use Wappointment\System\Data;

class ErrorStatusData extends Data
{
    public function __construct(#[Min(400), Max(599)] public readonly int $status) {}
}
