<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Booking;

use Wappointment\UseCases\Booking\GetBookingSettingsUseCase;
use Wappointment\Http\Requests\GetBookingSettingsRequestData;
use Wappointment\Http\Responses\GetBookingSettingsResponseData;

class GetBookingSettingsController
{
    public function __construct(private GetBookingSettingsUseCase $useCase) {}

    public function __invoke(GetBookingSettingsRequestData $requestData): GetBookingSettingsResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
