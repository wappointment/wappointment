<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Settings;

use Wappointment\Actions\Settings\GetSettingsAction;
use Wappointment\Http\Requests\GetSettingsRequestData;
use Wappointment\Http\Responses\GetSettingsResponseData;

class GetSettingsUseCase
{
    public function __construct(private GetSettingsAction $action) {}

    public function execute(GetSettingsRequestData $requestData): GetSettingsResponseData
    {
        return GetSettingsResponseData::from($this->action->handle());
    }
}
