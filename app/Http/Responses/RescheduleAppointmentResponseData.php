<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Attributes\In;

class RescheduleAppointmentResponseData extends ResponseData
{
    public function __construct(#[In([true])] public readonly bool $updated) {}
}
