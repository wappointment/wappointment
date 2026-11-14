<?php
declare(strict_types=1);

namespace Wappointment\Actions\Clients;

use Wappointment\Http\Requests\ClientCreateRequestData;
use Wappointment\Repositories\ClientRepository;
use Wappointment\Auth\AccessPolicy;

class CreateClientAction
{
    public function __construct(private ClientRepository $repository, private AccessPolicy $access)
    {
    }

    public function handle(ClientCreateRequestData $requestData): array
    {
        $this->access->assert('clients.create');
        $payload = $requestData->toArray();
        $payload['options'] = json_encode((object) $requestData->options, JSON_THROW_ON_ERROR);
        $payload['created_at'] = current_time('mysql');
        $payload['updated_at'] = current_time('mysql');
        return $this->repository->create($payload);
    }
}
