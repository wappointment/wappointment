<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Settings;

use Wappointment\Http\Requests\GetSettingsRequestData;
use Wappointment\Http\Responses\GetSettingsResponseData;
use Wappointment\UseCases\Settings\GetSettingsUseCase;

class GetSettingsController
{
    public function __construct(
        private GetSettingsUseCase $useCase
    ) {}

    public function __invoke(GetSettingsRequestData $requestData): GetSettingsResponseData
    {
        return $this->useCase->execute($requestData);
    }
}