<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Repositories\BookingRepository;

class ListAppointmentsAction
{
    public function __construct(private BookingRepository $repository) {}

    public function handle(): array
    {
        return ['appointments' => $this->repository->adminAppointments()];
    }
}
