<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Booking;

use Wappointment\Http\Requests\UpdateAppointmentStatusRequestData;
use Wappointment\Http\Responses\UpdateAppointmentStatusResponseData;
use Wappointment\UseCases\Booking\UpdateAppointmentStatusUseCase;

class UpdateAppointmentStatusController
{
    public function __construct(private UpdateAppointmentStatusUseCase $useCase) {}

    public function __invoke(UpdateAppointmentStatusRequestData $requestData): UpdateAppointmentStatusResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
