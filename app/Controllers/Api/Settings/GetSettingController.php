<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Settings;

use Wappointment\Http\Requests\GetSettingRequestData;
use Wappointment\Http\Responses\GetSettingResponseData;
use Wappointment\UseCases\Settings\GetSettingUseCase;

class GetSettingController
{
    public function __construct(
        private GetSettingUseCase $useCase
    ) {}

    public function __invoke(GetSettingRequestData $requestData): GetSettingResponseData
    {
        return $this->useCase->execute($requestData);
    }
}