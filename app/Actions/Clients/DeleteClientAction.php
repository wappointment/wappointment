<?php
declare(strict_types=1);

namespace Wappointment\Actions\Clients;

use Wappointment\Repositories\ClientRepository;

class DeleteClientAction
{
	public function __construct(private ClientRepository $repository)
	{
	}

	public function handle(int $id): array
	{
		$client = $this->repository->find($id);
		if (!$client) {
			throw new \RuntimeException('Client not found', 404);
		}

		$deleted = $this->repository->delete($id);
		if (!$deleted) {
			throw new \RuntimeException('Failed to delete client', 500);
		}

		return ['success' => true, 'message' => 'Client deleted'];
	}
}