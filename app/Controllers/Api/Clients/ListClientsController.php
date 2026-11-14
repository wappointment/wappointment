<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Clients;

use Wappointment\UseCases\Clients\ListClientsUseCase;
use Wappointment\Http\Responses\ListClientsResponseData;
use Wappointment\Http\Requests\ListClientsRequestData;

class ListClientsController
{
    public function __construct(
        private ListClientsUseCase $useCase
    ) {}

    public function __invoke(ListClientsRequestData $requestData): ListClientsResponseData
    {
        return $this->useCase->execute($requestData);
    }
}
