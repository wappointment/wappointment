<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Data\AppointmentData;

class ListAppointmentsResponseData extends ResponseData
{
    /** @param list<AppointmentData> $appointments */
    public function __construct(#[CollectionOf(AppointmentData::class)] public readonly array $appointments) {}
}
