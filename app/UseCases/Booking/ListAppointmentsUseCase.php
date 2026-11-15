<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Booking;

use Wappointment\Actions\Booking\ListAppointmentsAction;
use Wappointment\Http\Requests\ListAppointmentsRequestData;
use Wappointment\Http\Responses\ListAppointmentsResponseData;

class ListAppointmentsUseCase
{
    public function __construct(private ListAppointmentsAction $action) {}

    public function execute(ListAppointmentsRequestData $requestData): ListAppointmentsResponseData
    {
        return ListAppointmentsResponseData::from($this->action->handle([
            'from' => $requestData->from,
            'to' => $requestData->to,
            'calendar_id' => $requestData->calendarId,
            'service_id' => $requestData->serviceId,
            'page' => $requestData->page,
            'per_page' => $requestData->perPage,
        ]));
    }
}
