<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Auth\AccessPolicy;
use Wappointment\Repositories\BookingRepository;

class SetAppointmentLockAction
{
    public function __construct(private BookingRepository $repository, private AccessPolicy $access) {}

    public function handle(int $id, bool $locked): array
    {
        $this->access->assert('appointments.lock');
        $this->repository->setAppointmentLocked($id, $locked, $this->access->appointmentOwner());
        return ['updated' => true];
    }
}
