<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Clients;

use Wappointment\Actions\Clients\UpdateClientAction;
use Wappointment\Http\JsonErrorResponse;
use Wappointment\Http\JsonResponse;
use Wappointment\Http\Requests\UpdateClientRequestData;

class UpdateClientController
{
    public function __construct(
        private UpdateClientAction $action
    ) {}

    public function __invoke(UpdateClientRequestData $request): void
    {
        try {
            $result = $this->action->handle($request);
            JsonResponse::success($result);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() === 404 ? 404 : 500;
            JsonErrorResponse::send($e->getMessage(), $status);
        }
    }
}
