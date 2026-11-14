<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api\Clients;

use Wappointment\UseCases\Clients\UpdateClientUseCase;
use Wappointment\Http\Responses\UpdateClientResponseData;
use Wappointment\Http\Requests\UpdateClientRequestData;

class UpdateClientController
{
    public function __construct(
        private UpdateClientUseCase $useCase
    ) {}

    public function __invoke(UpdateClientRequestData $request): UpdateClientResponseData
    {
        return $this->useCase->execute($request);
    }
}
