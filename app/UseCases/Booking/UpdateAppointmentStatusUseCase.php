<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Booking;

use Wappointment\Actions\Booking\UpdateAppointmentStatusAction;
use Wappointment\Http\Requests\UpdateAppointmentStatusRequestData;
use Wappointment\Http\Responses\UpdateAppointmentStatusResponseData;

class UpdateAppointmentStatusUseCase
{
    public function __construct(private UpdateAppointmentStatusAction $action) {}

    public function execute(UpdateAppointmentStatusRequestData $requestData): UpdateAppointmentStatusResponseData
    {
        return new UpdateAppointmentStatusResponseData($this->action->handle($requestData->toArray()));
    }
}
