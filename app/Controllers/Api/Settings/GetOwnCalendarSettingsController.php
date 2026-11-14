<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Settings;

use Wappointment\UseCases\Settings\GetOwnCalendarSettingsUseCase;
use Wappointment\Http\Requests\GetOwnCalendarSettingsRequestData;
use Wappointment\Http\Responses\GetOwnCalendarSettingsResponseData;

class GetOwnCalendarSettingsController
{
    public function __construct(private GetOwnCalendarSettingsUseCase $useCase) {}

    public function __invoke(GetOwnCalendarSettingsRequestData $requestData): GetOwnCalendarSettingsResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
