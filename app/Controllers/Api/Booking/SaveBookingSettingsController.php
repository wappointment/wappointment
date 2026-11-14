<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Booking;

use Wappointment\Http\Requests\SaveBookingSettingsRequestData;
use Wappointment\Http\Responses\SaveBookingSettingsResponseData;
use Wappointment\UseCases\Booking\SaveBookingSettingsUseCase;

class SaveBookingSettingsController
{
    public function __construct(private SaveBookingSettingsUseCase $useCase) {}

    public function __invoke(SaveBookingSettingsRequestData $requestData): SaveBookingSettingsResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
