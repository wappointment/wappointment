<?php
declare(strict_types=1);

namespace Wappointment\Actions\Clients;

use Wappointment\Http\Requests\UpdateClientRequestData;
use Wappointment\Repositories\ClientRepository;

class UpdateClientAction
{
	public function __construct(private ClientRepository $repository)
	{
	}

	public function handle(UpdateClientRequestData $requestData): array
	{
		$client = $this->repository->find($requestData->id);
		if (!$client) {
			throw new \RuntimeException('Client not found', 404);
		}

		$updateData = $requestData->updatePayload();

		$updated = $this->repository->update($requestData->id, $updateData);
		if ($updated === false) {
			throw new \RuntimeException('Failed to update client', 500);
		}

		return $this->repository->find($requestData->id);
	}
}