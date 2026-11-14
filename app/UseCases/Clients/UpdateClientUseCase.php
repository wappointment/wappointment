<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Clients;

use Wappointment\Actions\Clients\UpdateClientAction;
use Wappointment\Http\Requests\UpdateClientRequestData;
use Wappointment\Http\Responses\UpdateClientResponseData;

class UpdateClientUseCase
{
    public function __construct(private UpdateClientAction $action) {}

    public function execute(UpdateClientRequestData $requestData): UpdateClientResponseData
    {
        return new UpdateClientResponseData($this->action->handle($requestData));
    }
}
