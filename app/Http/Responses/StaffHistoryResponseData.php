<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Data\HistoryAppointmentData;

class StaffHistoryResponseData extends ResponseData
{
    /** @param list<HistoryAppointmentData> $appointments */
    public function __construct(
        #[CollectionOf(HistoryAppointmentData::class)] public readonly array $appointments
    ) {}
}
