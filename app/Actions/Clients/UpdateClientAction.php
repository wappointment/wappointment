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

		$updateData = array_filter(
			['name' => $requestData->name, 'email' => $requestData->email, 'options' => $requestData->options],
			static fn(mixed $value): bool => $value !== null
		);
		if ($requestData->options !== null) {
			$updateData['options'] = json_encode($requestData->options, JSON_THROW_ON_ERROR);
		}
		$updateData['updated_at'] = current_time('mysql');

		$updated = $this->repository->update($requestData->id, $updateData);
		if ($updated === false) {
			throw new \RuntimeException('Failed to update client', 500);
		}

		return $this->repository->find($requestData->id);
	}
}