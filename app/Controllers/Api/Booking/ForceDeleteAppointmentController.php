<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Booking;

use Wappointment\Http\Requests\ForceDeleteAppointmentRequestData;
use Wappointment\Http\Responses\ForceDeleteAppointmentResponseData;
use Wappointment\UseCases\Booking\ForceDeleteAppointmentUseCase;

class ForceDeleteAppointmentController
{
    public function __construct(private ForceDeleteAppointmentUseCase $useCase) {}

    public function __invoke(ForceDeleteAppointmentRequestData $requestData): ForceDeleteAppointmentResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
