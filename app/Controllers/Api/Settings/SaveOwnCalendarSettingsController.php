<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Settings;

use Wappointment\UseCases\Settings\SaveOwnCalendarSettingsUseCase;
use Wappointment\Http\Requests\SaveOwnCalendarSettingsRequestData;
use Wappointment\Http\Responses\SaveOwnCalendarSettingsResponseData;

class SaveOwnCalendarSettingsController
{
    public function __construct(private SaveOwnCalendarSettingsUseCase $useCase) {}

    public function __invoke(SaveOwnCalendarSettingsRequestData $requestData): SaveOwnCalendarSettingsResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
