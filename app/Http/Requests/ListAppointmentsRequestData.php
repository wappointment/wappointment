<?php
declare(strict_types=1);

namespace Wappointment\Http\Requests;

use Wappointment\Http\Attributes\MapInputName;
use Wappointment\Http\Attributes\Max;
use Wappointment\Http\Attributes\Min;

class ListAppointmentsRequestData extends RequestData
{
    public function __construct(
        public readonly ?int $from = null,
        public readonly ?int $to = null,
        #[MapInputName('calendar_id'), Min(1)] public readonly ?int $calendarId = null,
        #[MapInputName('service_id'), Min(1)] public readonly ?int $serviceId = null,
        #[Min(1), Max(100000)] public readonly int $page = 1,
        #[MapInputName('per_page'), Min(1), Max(100)] public readonly int $perPage = 100,
    ) {}
}
