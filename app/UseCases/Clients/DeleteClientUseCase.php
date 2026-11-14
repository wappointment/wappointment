<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Clients;

use Wappointment\Actions\Clients\DeleteClientAction;
use Wappointment\Http\Requests\DeleteClientRequestData;
use Wappointment\Http\Responses\DeleteClientResponseData;

class DeleteClientUseCase
{
    public function __construct(private DeleteClientAction $action) {}

    public function execute(DeleteClientRequestData $requestData): DeleteClientResponseData
    {
        return DeleteClientResponseData::from($this->action->handle($requestData->id));
    }
}
