<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Actions\Jobs\QueueBookingNotificationsAction;
use Wappointment\Booking\BookingException;
use Wappointment\Jobs\BookingNotificationPlan;
use Wappointment\Repositories\BookingRepository;
use Wappointment\Auth\AccessPolicy;

class UpdateAppointmentStatusAction
{
    public function __construct(
        private BookingRepository $repository,
        private AccessPolicy $access,
        private QueueBookingNotificationsAction $notifications
    ) {}

    public function handle(array $data): array
    {
        if (!in_array($data['status'] ?? null, [-1, 1], true)
            || filter_var($data['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            throw new BookingException(__('Supply an appointment ID and a valid status.', 'wappointment'));
        }
        $this->access->assert($data['status'] === 1 ? 'appointments.confirm' : 'appointments.cancel');
        $transition = $data['status'] === 1 ? BookingNotificationPlan::CONFIRMED : BookingNotificationPlan::CANCELED;
        $this->repository->setStatus(
            (int) $data['id'],
            $data['status'],
            $this->access->appointmentOwner(),
            function (array $appointment, array $previous) use ($transition): void {
                // Canceling an already-canceled appointment is not a new transition.
                if ($transition === BookingNotificationPlan::CONFIRMED || (int) $previous['status'] >= 0) {
                    $this->notifications->handle($transition, $appointment);
                }
            }
        );
        return ['updated' => true];
    }
}
