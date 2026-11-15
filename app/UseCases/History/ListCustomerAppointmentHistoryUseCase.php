<?php
declare(strict_types=1);

namespace Wappointment\UseCases\History;

use Wappointment\Actions\History\ListCustomerAppointmentHistoryAction;
use Wappointment\Http\Requests\CustomerHistoryRequestData;
use Wappointment\Http\Responses\CustomerHistoryResponseData;

class ListCustomerAppointmentHistoryUseCase
{
    public function __construct(private ListCustomerAppointmentHistoryAction $action) {}

    public function execute(CustomerHistoryRequestData $requestData): CustomerHistoryResponseData
    {
        return CustomerHistoryResponseData::from([
            'appointments' => $this->action->handle($requestData->email),
        ]);
    }
}
