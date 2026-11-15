<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\MapInputName;
use Wappointment\Http\Attributes\Min;

class ForceDeleteAppointmentRequestData extends RequestData
{
    public function __construct(#[MapInputName('id'), Min(1)] public readonly int $id) {}
}
