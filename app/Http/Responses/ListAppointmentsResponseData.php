<?php
declare(strict_types=1);

namespace Wappointment\Http\Responses;

use Wappointment\Http\Attributes\CollectionOf;
use Wappointment\Http\Attributes\MapInputName;
use Wappointment\Http\Data\AppointmentCalendarOptionData;
use Wappointment\Http\Data\AppointmentData;
use Wappointment\Http\Data\AppointmentLocationOptionData;
use Wappointment\Http\Data\AppointmentServiceOptionData;
use Wappointment\Http\Data\CalendarEventData;

class ListAppointmentsResponseData extends ResponseData
{
    /** @param list<AppointmentData> $appointments */
    public function __construct(
        #[CollectionOf(AppointmentData::class)] public readonly array $appointments,
        public readonly int $total = 0,
        public readonly int $page = 1,
        #[MapInputName('per_page')]
        public readonly int $perPage = 100,
        #[CollectionOf(AppointmentCalendarOptionData::class)]
        public readonly array $calendars = [],
        #[CollectionOf(AppointmentServiceOptionData::class)]
        public readonly array $services = [],
        #[CollectionOf(AppointmentLocationOptionData::class)]
        public readonly array $locations = [],
        #[CollectionOf(CalendarEventData::class)]
        public readonly array $events = [],
    ) {}
}
