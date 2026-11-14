<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Booking;

use Wappointment\Actions\Booking\SaveBookingSettingsAction;
use Wappointment\Http\Requests\SaveBookingSettingsRequestData;
use Wappointment\Http\Responses\SaveBookingSettingsResponseData;

class SaveBookingSettingsUseCase
{
    public function __construct(private SaveBookingSettingsAction $action) {}

    public function execute(SaveBookingSettingsRequestData $requestData): SaveBookingSettingsResponseData
    {
        return new SaveBookingSettingsResponseData($this->action->handle($requestData->toArray()));
    }
}
