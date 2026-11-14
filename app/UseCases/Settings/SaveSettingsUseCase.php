<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Settings;

use Wappointment\Actions\Settings\SaveSettingsAction;
use Wappointment\Http\Requests\SaveSettingsRequestData;
use Wappointment\Http\Responses\SaveSettingsResponseData;

class SaveSettingsUseCase
{
    public function __construct(private SaveSettingsAction $action) {}

    public function execute(SaveSettingsRequestData $requestData): SaveSettingsResponseData
    {
        return new SaveSettingsResponseData($this->action->handle($requestData->toArray()));
    }
}
