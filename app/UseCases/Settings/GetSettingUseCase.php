<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Settings;

use Wappointment\Actions\Settings\GetSettingAction;
use Wappointment\Http\Requests\GetSettingRequestData;
use Wappointment\Http\Responses\GetSettingResponseData;

class GetSettingUseCase
{
    public function __construct(private GetSettingAction $action) {}

    public function execute(GetSettingRequestData $requestData): GetSettingResponseData
    {
        return GetSettingResponseData::from($this->action->handle($requestData->name));
    }
}
