<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Booking;

use Wappointment\Actions\Booking\DeleteAppointmentAction;
use Wappointment\Http\Requests\ForceDeleteAppointmentRequestData;
use Wappointment\Http\Responses\ForceDeleteAppointmentResponseData;

class ForceDeleteAppointmentUseCase
{
    public function __construct(private DeleteAppointmentAction $action) {}

    public function execute(ForceDeleteAppointmentRequestData $requestData): ForceDeleteAppointmentResponseData
    {
        return ForceDeleteAppointmentResponseData::from($this->action->handle($requestData->id, true));
    }
}
