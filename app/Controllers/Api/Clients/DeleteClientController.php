<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Clients;

use Wappointment\UseCases\Clients\DeleteClientUseCase;
use Wappointment\Http\Responses\DeleteClientResponseData;
use Wappointment\Http\Requests\DeleteClientRequestData;

class DeleteClientController
{
    public function __construct(
        private DeleteClientUseCase $useCase
    ) {}

    public function __invoke(DeleteClientRequestData $request): DeleteClientResponseData
    {
        return $this->useCase->execute($request);
    }
}