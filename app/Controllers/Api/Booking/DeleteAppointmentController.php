<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Booking;

use Wappointment\Http\Requests\DeleteAppointmentRequestData;
use Wappointment\Http\Responses\DeleteAppointmentResponseData;
use Wappointment\UseCases\Booking\DeleteAppointmentUseCase;

class DeleteAppointmentController
{
    public function __construct(private DeleteAppointmentUseCase $useCase) {}

    public function __invoke(DeleteAppointmentRequestData $requestData): DeleteAppointmentResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
