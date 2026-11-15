<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Settings;

use Wappointment\Actions\Settings\ListOwnCalendarStatusesAction;
use Wappointment\Http\Requests\GetOwnCalendarStatusesRequestData;
use Wappointment\Http\Responses\ListOwnCalendarStatusesResponseData;

class ListOwnCalendarStatusesUseCase
{
    public function __construct(private ListOwnCalendarStatusesAction $action) {}

    public function execute(GetOwnCalendarStatusesRequestData $requestData): ListOwnCalendarStatusesResponseData
    {
        return ListOwnCalendarStatusesResponseData::from($this->action->handle($requestData));
    }
}
