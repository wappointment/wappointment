<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Settings;

use Wappointment\Http\Requests\SaveSettingsRequestData;
use Wappointment\Http\Responses\SaveSettingsResponseData;
use Wappointment\UseCases\Settings\SaveSettingsUseCase;

class SaveSettingsController
{
    public function __construct(
        private SaveSettingsUseCase $useCase
    ) {}

    public function __invoke(SaveSettingsRequestData $requestData): SaveSettingsResponseData
    {
        return $this->useCase->execute($requestData);
    }
}