<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Settings;

use Wappointment\Actions\Settings\CreateOwnCalendarStatusAction;
use Wappointment\Http\Requests\CreateOwnCalendarStatusRequestData;
use Wappointment\Http\Responses\CreateOwnCalendarStatusResponseData;

class CreateOwnCalendarStatusUseCase
{
    public function __construct(private CreateOwnCalendarStatusAction $action) {}

    public function execute(CreateOwnCalendarStatusRequestData $requestData): CreateOwnCalendarStatusResponseData
    {
        return CreateOwnCalendarStatusResponseData::from($this->action->handle($requestData));
    }
}
