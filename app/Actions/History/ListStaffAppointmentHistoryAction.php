<?php
declare(strict_types=1);

namespace Wappointment\Actions\History;

use Wappointment\Repositories\HistoryRepository;

class ListStaffAppointmentHistoryAction
{
    public function __construct(private HistoryRepository $repository) {}

    public function handle(int $userId): array
    {
        return $this->repository->staffAppointments($userId);
    }
}
