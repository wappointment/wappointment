<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Settings;

use Wappointment\Actions\Settings\DeleteOwnCalendarStatusAction;
use Wappointment\Http\Requests\DeleteOwnCalendarStatusRequestData;
use Wappointment\Http\Responses\DeleteOwnCalendarStatusResponseData;

class DeleteOwnCalendarStatusUseCase
{
    public function __construct(private DeleteOwnCalendarStatusAction $action) {}

    public function execute(DeleteOwnCalendarStatusRequestData $requestData): DeleteOwnCalendarStatusResponseData
    {
        return DeleteOwnCalendarStatusResponseData::from($this->action->handle($requestData));
    }
}
