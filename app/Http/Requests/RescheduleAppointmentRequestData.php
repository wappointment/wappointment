<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\Date;
use Wappointment\Http\Attributes\MapInputName;
use Wappointment\Http\Attributes\Min;

class RescheduleAppointmentRequestData extends RequestData
{
    public function __construct(
        #[MapInputName('id'), Min(1)] public readonly int $id,
        #[Date] public readonly string $date,
        #[Min(1)] public readonly int $start,
    ) {}
}
