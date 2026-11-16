<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Actions\Jobs\QueueBookingNotificationsAction;
use Wappointment\Auth\AccessPolicy;
use Wappointment\Jobs\BookingNotificationPlan;
use Wappointment\Booking\BookingException;
use Wappointment\Http\Requests\RescheduleAppointmentRequestData;
use Wappointment\Repositories\BookingRepository;

class RescheduleAppointmentAction
{
    public function __construct(
        private BookingRepository $repository,
        private AccessPolicy $access,
        private GetRescheduleInputAction $rescheduleInput,
        private GetSelectedSlotAction $selectedSlot,
        private QueueBookingNotificationsAction $notifications
    ) {}

    public function handle(RescheduleAppointmentRequestData $input): array
    {
        $this->access->assert('appointments.edit');
        $owner = $this->access->appointmentOwner();
        return $this->repository->exclusive(function () use ($input, $owner): array {
            $appointment = $this->repository->adminAppointment($input->id, $owner, true);
            if (!empty($appointment['recurrent']) || !empty($appointment['parent'])) {
                throw new BookingException(__('Recurring appointment operations are unavailable until Group support is migrated.', 'wappointment'), 409);
            }
            if (!empty($appointment['options']['providers'])) {
                throw new BookingException(__('This appointment is linked to an external provider that v3 cannot safely update.', 'wappointment'), 409);
            }
            $selection = $this->rescheduleInput->handle($appointment, $input->date, $input->start);
            $slot = $this->selectedSlot->handle(
                $selection,
                (int) $appointment['id'],
                (int) ($appointment['options']['buffer_time'] ?? 0)
            );
            $options = $appointment['options'];
            $options['sequence'] = (int) ($options['sequence'] ?? 0) + 1;
            $changes = [
                'start_at' => gmdate('Y-m-d H:i:s', $slot['start']),
                'end_at' => gmdate('Y-m-d H:i:s', $slot['reserved_until']),
                'options' => wp_json_encode($options, JSON_THROW_ON_ERROR),
                'updated_at' => gmdate('Y-m-d H:i:s'),
            ];
            $this->repository->updateAppointment((int) $appointment['id'], $changes);
            $this->notifications->handle(BookingNotificationPlan::RESCHEDULED, array_merge($appointment, $changes), $appointment);
            return ['updated' => true];
        });
    }
}
