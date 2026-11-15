<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Settings;

use Wappointment\Actions\Settings\UpdateOwnCalendarStatusAction;
use Wappointment\Http\Requests\UpdateOwnCalendarStatusRequestData;
use Wappointment\Http\Responses\UpdateOwnCalendarStatusResponseData;

class UpdateOwnCalendarStatusUseCase
{
    public function __construct(private UpdateOwnCalendarStatusAction $action) {}

    public function execute(UpdateOwnCalendarStatusRequestData $requestData): UpdateOwnCalendarStatusResponseData
    {
        return UpdateOwnCalendarStatusResponseData::from($this->action->handle($requestData));
    }
}
