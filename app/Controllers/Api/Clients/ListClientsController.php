<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Clients;

use Wappointment\Actions\Clients\ListClientsAction;
use Wappointment\Http\JsonResponse;
use Wappointment\Http\Requests\ListClientsRequestData;

class ListClientsController
{
    public function __construct(
        private ListClientsAction $action
    ) {}

    public function __invoke(ListClientsRequestData $requestData): void
    {
        $result = $this->action->handle($requestData);

        JsonResponse::send($result);
    }
}
