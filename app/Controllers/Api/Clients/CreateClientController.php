<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Clients;

use Wappointment\Actions\Clients\CreateClientAction;
use Wappointment\Http\JsonErrorResponse;
use Wappointment\Http\JsonResponse;
use Wappointment\Http\Requests\ClientCreateRequestData;

class CreateClientController
{
    public function __construct(
        private CreateClientAction $action
    ) {}

    public function __invoke(ClientCreateRequestData $requestData): void
    {
        try {
            $result = $this->action->handle($requestData);
            JsonResponse::success($result, 201);
        } catch (\InvalidArgumentException $e) {
            JsonErrorResponse::send($e->getMessage(), 400);
        } catch (\RuntimeException $e) {
            JsonErrorResponse::send($e->getMessage(), 500);
        }
    }
}