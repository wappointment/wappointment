<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Clients;

use Wappointment\Actions\Clients\CreateClientAction;
use Wappointment\Http\Requests\ClientCreateRequestData;
use Wappointment\Http\Responses\CreateClientResponseData;

class CreateClientUseCase
{
    public function __construct(private CreateClientAction $action) {}

    public function execute(ClientCreateRequestData $requestData): CreateClientResponseData
    {
        return new CreateClientResponseData($this->action->handle($requestData));
    }
}
