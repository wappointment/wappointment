<?php
declare(strict_types=1);

namespace Wappointment\Actions\Clients;

use Wappointment\Http\Requests\ListClientsRequestData;
use Wappointment\Repositories\ClientRepository;

class ListClientsAction
{
	public function __construct(private ClientRepository $repository)
	{
	}

	public function handle(ListClientsRequestData $requestData): array
	{
		if ($requestData->search !== '') {
			return $this->repository->search($requestData->search, $requestData->page, $requestData->perPage);
		}

		return $this->repository->paginate($requestData->page, $requestData->perPage);
	}
}