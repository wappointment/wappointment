<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Booking;

use Wappointment\Actions\Booking\SetAppointmentLockAction;
use Wappointment\Http\Requests\SetAppointmentLockRequestData;
use Wappointment\Http\Responses\SetAppointmentLockResponseData;

class SetAppointmentLockUseCase
{
    public function __construct(private SetAppointmentLockAction $action) {}

    public function execute(SetAppointmentLockRequestData $requestData): SetAppointmentLockResponseData
    {
        return SetAppointmentLockResponseData::from($this->action->handle($requestData->id, $requestData->locked));
    }
}
