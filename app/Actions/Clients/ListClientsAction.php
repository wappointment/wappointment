<?php
declare(strict_types=1);

namespace Wappointment\Actions\Clients;

use Wappointment\Http\Requests\ListClientsRequestData;
use Wappointment\Repositories\ClientRepository;
use Wappointment\Auth\AccessPolicy;

class ListClientsAction
{
	public function __construct(private ClientRepository $repository, private AccessPolicy $access)
	{
	}

	public function handle(ListClientsRequestData $requestData): array
	{
		return $this->repository->listVisible(
			$requestData->search, $requestData->page, $requestData->perPage, $this->access->clientOwner()
		);
	}
}