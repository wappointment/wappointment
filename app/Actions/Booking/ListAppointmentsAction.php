<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Repositories\BookingRepository;
use Wappointment\Auth\AccessPolicy;
use Wappointment\Booking\BookingException;

class ListAppointmentsAction
{
    public function __construct(private BookingRepository $repository, private AccessPolicy $access) {}

    public function handle(array $filters = []): array
    {
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        if (($from === null) !== ($to === null)) {
            throw new BookingException(__('Both range boundaries are required.', 'wappointment'), 400);
        }
        if ($from !== null && ($to <= $from || $to - $from > YEAR_IN_SECONDS)) {
            throw new BookingException(__('Appointment range must be positive and no longer than one year.', 'wappointment'), 400);
        }

        return $this->repository->adminAppointments($this->access->appointmentOwner(), $filters);
    }
}
