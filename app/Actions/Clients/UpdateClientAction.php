<?php
declare(strict_types=1);

namespace Wappointment\Actions\Clients;

use Wappointment\Http\Requests\UpdateClientRequestData;
use Wappointment\Repositories\ClientRepository;
use Wappointment\System\Optional;
use Wappointment\Auth\AccessPolicy;

class UpdateClientAction
{
	public function __construct(private ClientRepository $repository, private AccessPolicy $access)
	{
	}

	public function handle(UpdateClientRequestData $requestData): array
	{
		$this->access->assert('clients.edit');
		$updateData = array_filter(
			['name' => $requestData->name, 'email' => $requestData->email, 'options' => $requestData->options],
			static fn(mixed $value): bool => !$value instanceof Optional
		);
		if (!$requestData->options instanceof Optional) {
			$updateData['options'] = json_encode((object) $requestData->options, JSON_THROW_ON_ERROR);
		}
		$updateData['updated_at'] = current_time('mysql');

		return $this->repository->updateVisible($requestData->id, $updateData, $this->access->clientOwner());
	}
}