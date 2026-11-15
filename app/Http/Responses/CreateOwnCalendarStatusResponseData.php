<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Data\OwnCalendarStatusData;

class CreateOwnCalendarStatusResponseData extends ResponseData
{
    public function __construct(public readonly OwnCalendarStatusData $status) {}
}
