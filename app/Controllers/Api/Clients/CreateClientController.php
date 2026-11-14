<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Clients;

use Wappointment\UseCases\Clients\CreateClientUseCase;
use Wappointment\Http\Responses\CreateClientResponseData;
use Wappointment\Http\Requests\ClientCreateRequestData;

class CreateClientController
{
    public function __construct(
        private CreateClientUseCase $useCase
    ) {}

    public function __invoke(ClientCreateRequestData $requestData): CreateClientResponseData
    {
        return $this->useCase->execute($requestData);
    }
}