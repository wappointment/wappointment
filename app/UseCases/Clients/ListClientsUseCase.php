<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Clients;

use Wappointment\Actions\Clients\ListClientsAction;
use Wappointment\Http\Requests\ListClientsRequestData;
use Wappointment\Http\Responses\ListClientsResponseData;

class ListClientsUseCase
{
    public function __construct(private ListClientsAction $action) {}

    public function execute(ListClientsRequestData $requestData): ListClientsResponseData
    {
        return new ListClientsResponseData($this->action->handle($requestData));
    }
}
