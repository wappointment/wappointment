<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Clients;

use Wappointment\Actions\Clients\DeleteClientAction;
use Wappointment\Http\JsonErrorResponse;
use Wappointment\Http\JsonResponse;
use Wappointment\Http\Requests\DeleteClientRequestData;

class DeleteClientController
{
    public function __construct(
        private DeleteClientAction $action
    ) {}

    public function __invoke(DeleteClientRequestData $request): void
    {
        try {
            JsonResponse::send(
                $this->action->handle($request->id)
            );
        } catch (\RuntimeException $e) {
            $status = $e->getCode() === 404 ? 404 : 500;
            JsonErrorResponse::send($e->getMessage(), $status);
        }
    }
}