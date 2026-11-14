<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Http\Requests\BookingRequestData;
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

    public function handle(BookingRequestData $input, string $action): array
    {
        return $this->repository->exclusive(function () use ($input, $action): array {
            $appointment = $this->getAppointment->handle($input);
            $this->assertChangeAllowed->handle($appointment, $action);
            $changes = ['updated_at' => gmdate('Y-m-d H:i:s')];
            if ($action === 'cancel') {
                $changes['status'] = -1;
            } else {
                $slot = $this->selectedSlot->handle(
                    $this->rescheduleInput->handle($appointment, $input), (int) $appointment['id'],
                    (int) ($appointment['options']['buffer_time'] ?? 0)
                );
                $changes['start_at'] = gmdate('Y-m-d H:i:s', $slot['start']);
                $changes['end_at'] = gmdate('Y-m-d H:i:s', $slot['reserved_until']);
            }
            $this->repository->updateAppointment((int) $appointment['id'], $changes);
            return array_merge($appointment, $changes);
        });
    }
}
