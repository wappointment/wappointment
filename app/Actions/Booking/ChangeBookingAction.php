<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Http\Requests\ManageBookingRequestData;
use Wappointment\Repositories\BookingRepository;

class ChangeBookingAction
{
    public function __construct(
        private BookingRepository $repository,
        private GetAppointmentByKeyAction $getAppointment,
        private AssertBookingChangeAllowedAction $assertChangeAllowed,
        private GetRescheduleInputAction $rescheduleInput,
        private GetSelectedSlotAction $selectedSlot
    ) {}

    public function handle(ManageBookingRequestData $input, string $action): array
    {
        return $this->repository->exclusive(function () use ($input, $action): array {
            $appointment = $this->getAppointment->handle($input->edit_key);
            $this->assertChangeAllowed->handle($appointment, $action);
            $changes = ['updated_at' => gmdate('Y-m-d H:i:s')];
            if ($action === 'cancel') {
                $changes['status'] = -1;
            } else {
                $slot = $this->selectedSlot->handle(
                    $this->rescheduleInput->handle($appointment, $input->date, $input->start), (int) $appointment['id'],
                    (int) ($appointment['options']['buffer_time'] ?? 0)
                );
                $changes['start_at'] = gmdate('Y-m-d H:i:s', $slot['start']);
                $changes['end_at'] = gmdate('Y-m-d H:i:s', $slot['reserved_until']);
                $options = $appointment['options'];
                $options['sequence'] = (int) ($options['sequence'] ?? 0) + 1;
                $changes['options'] = wp_json_encode($options, JSON_THROW_ON_ERROR);
            }
            $this->repository->updateAppointment((int) $appointment['id'], $changes);
            return array_merge($appointment, $changes);
        });
    }
}
