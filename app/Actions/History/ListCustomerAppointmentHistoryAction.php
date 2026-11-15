<?php
declare(strict_types=1);

namespace Wappointment\Actions\History;

use Wappointment\Repositories\HistoryRepository;

class ListCustomerAppointmentHistoryAction
{
    public function __construct(private HistoryRepository $repository) {}

    public function handle(string $email): array
    {
        return $this->repository->customerAppointments($email);
    }
}
