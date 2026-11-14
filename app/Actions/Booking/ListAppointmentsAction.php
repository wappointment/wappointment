<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Repositories\BookingRepository;
use Wappointment\Auth\AccessPolicy;

class ListAppointmentsAction
{
    public function __construct(private BookingRepository $repository, private AccessPolicy $access) {}

    public function handle(): array
    {
        return ['appointments' => $this->repository->adminAppointments($this->access->appointmentOwner())];
    }
}
