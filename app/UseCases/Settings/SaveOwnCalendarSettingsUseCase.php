<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Settings;

use Wappointment\Actions\Settings\SaveOwnCalendarSettingsAction;
use Wappointment\Http\Requests\SaveOwnCalendarSettingsRequestData;
use Wappointment\Http\Responses\SaveOwnCalendarSettingsResponseData;

class SaveOwnCalendarSettingsUseCase
{
    public function __construct(private SaveOwnCalendarSettingsAction $action) {}

    public function execute(SaveOwnCalendarSettingsRequestData $requestData): SaveOwnCalendarSettingsResponseData
    {
        return SaveOwnCalendarSettingsResponseData::from($this->action->handle($requestData));
    }
}
