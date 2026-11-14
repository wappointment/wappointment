<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Settings;

use Wappointment\Actions\Settings\GetOwnCalendarSettingsAction;
use Wappointment\Http\Requests\GetOwnCalendarSettingsRequestData;
use Wappointment\Http\Responses\GetOwnCalendarSettingsResponseData;

class GetOwnCalendarSettingsUseCase
{
    public function __construct(private GetOwnCalendarSettingsAction $action) {}

    public function execute(GetOwnCalendarSettingsRequestData $requestData): GetOwnCalendarSettingsResponseData
    {
        return GetOwnCalendarSettingsResponseData::from($this->action->handle());
    }
}
