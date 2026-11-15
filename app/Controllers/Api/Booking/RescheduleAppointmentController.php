<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Booking;

use Wappointment\Http\Requests\RescheduleAppointmentRequestData;
use Wappointment\Http\Responses\RescheduleAppointmentResponseData;
use Wappointment\UseCases\Booking\RescheduleAppointmentUseCase;

class RescheduleAppointmentController
{
    public function __construct(private RescheduleAppointmentUseCase $useCase) {}

    public function __invoke(RescheduleAppointmentRequestData $requestData): RescheduleAppointmentResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
