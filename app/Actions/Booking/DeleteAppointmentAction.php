<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Auth\AccessPolicy;
use Wappointment\Repositories\BookingRepository;

class DeleteAppointmentAction
{
    public function __construct(private BookingRepository $repository, private AccessPolicy $access) {}

    public function handle(int $id, bool $force = false): array
    {
        $this->access->assert($force ? 'appointments.force_delete' : 'appointments.delete');
        $owner = $force ? null : $this->access->appointmentOwner();
        $this->repository->deleteAdminAppointment($id, $owner, $force);
        return ['deleted' => true];
    }
}
