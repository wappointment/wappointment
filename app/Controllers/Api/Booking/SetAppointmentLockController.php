<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Booking;

use Wappointment\Http\Requests\SetAppointmentLockRequestData;
use Wappointment\Http\Responses\SetAppointmentLockResponseData;
use Wappointment\UseCases\Booking\SetAppointmentLockUseCase;

class SetAppointmentLockController
{
    public function __construct(private SetAppointmentLockUseCase $useCase) {}

    public function __invoke(SetAppointmentLockRequestData $requestData): SetAppointmentLockResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
