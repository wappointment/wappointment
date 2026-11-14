<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Booking;

use Wappointment\Actions\Booking\GetBookingSettingsAction;
use Wappointment\Http\Requests\GetBookingSettingsRequestData;
use Wappointment\Http\Responses\GetBookingSettingsResponseData;

class GetBookingSettingsUseCase
{
    public function __construct(private GetBookingSettingsAction $action) {}

    public function execute(GetBookingSettingsRequestData $requestData): GetBookingSettingsResponseData
    {
        return GetBookingSettingsResponseData::from($this->action->handle());
    }
}
