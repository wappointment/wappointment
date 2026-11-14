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
        return new ListAppointmentsResponseData($this->action->handle());
    }
}
