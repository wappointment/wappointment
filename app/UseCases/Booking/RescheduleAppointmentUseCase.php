<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Booking;

use Wappointment\Actions\Booking\RescheduleAppointmentAction;
use Wappointment\Http\Requests\RescheduleAppointmentRequestData;
use Wappointment\Http\Responses\RescheduleAppointmentResponseData;

class RescheduleAppointmentUseCase
{
    public function __construct(private RescheduleAppointmentAction $action) {}

    public function execute(RescheduleAppointmentRequestData $requestData): RescheduleAppointmentResponseData
    {
        return RescheduleAppointmentResponseData::from($this->action->handle($requestData));
    }
}
