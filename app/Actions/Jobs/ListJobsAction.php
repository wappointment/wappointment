<?php
declare(strict_types=1);

namespace Wappointment\Actions\Jobs;

use Wappointment\Repositories\JobRepository;

class ListJobsAction
{
    public function __construct(private JobRepository $repository) {}

    public function handle(int $page, int $perPage): array
    {
        return $this->repository->paginate($page, $perPage);
    }
}
