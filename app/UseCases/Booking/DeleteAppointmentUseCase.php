<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Booking;

use Wappointment\Actions\Booking\DeleteAppointmentAction;
use Wappointment\Http\Requests\DeleteAppointmentRequestData;
use Wappointment\Http\Responses\DeleteAppointmentResponseData;

class DeleteAppointmentUseCase
{
    public function __construct(private DeleteAppointmentAction $action) {}

    public function execute(DeleteAppointmentRequestData $requestData): DeleteAppointmentResponseData
    {
        return DeleteAppointmentResponseData::from($this->action->handle($requestData->id));
    }
}
