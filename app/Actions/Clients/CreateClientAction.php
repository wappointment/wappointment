<?php
declare(strict_types=1);

namespace Wappointment\Actions\Clients;

use Wappointment\Http\Requests\ClientCreateRequestData;
use Wappointment\Repositories\ClientRepository;

class CreateClientAction
{
    public function __construct(private ClientRepository $repository)
    {
    }

    public function handle(ClientCreateRequestData $requestData): array
    {
        $payload = $requestData->toArray();
        $id = $this->repository->create($payload);

        if (!$id) {
            throw new \RuntimeException('Failed to create client');
        }

        $client = $this->repository->find($id);

        if (!$client) {
            throw new \RuntimeException('Failed to load created client');
        }

        return $client;
    }
}
