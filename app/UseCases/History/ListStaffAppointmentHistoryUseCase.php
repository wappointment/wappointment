<?php
declare(strict_types=1);

namespace Wappointment\UseCases\History;

use Wappointment\Actions\History\ListStaffAppointmentHistoryAction;
use Wappointment\Http\Requests\StaffHistoryRequestData;
use Wappointment\Http\Responses\StaffHistoryResponseData;

class ListStaffAppointmentHistoryUseCase
{
    public function __construct(private ListStaffAppointmentHistoryAction $action) {}

    public function execute(StaffHistoryRequestData $requestData): StaffHistoryResponseData
    {
        return StaffHistoryResponseData::from([
            'appointments' => $this->action->handle($requestData->user_id),
        ]);
    }
}
