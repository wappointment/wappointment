<?php
declare(strict_types=1);

namespace Wappointment\Actions\Clients;

use Wappointment\Repositories\ClientRepository;
use Wappointment\Auth\AccessPolicy;

class DeleteClientAction
{
	public function __construct(private ClientRepository $repository, private AccessPolicy $access)
	{
	}

	public function handle(int $id): array
	{
		$this->access->assert('clients.delete');
		$this->repository->deleteVisible($id, $this->access->clientOwner());
		return ['success' => true, 'message' => 'Client deleted'];
	}
}