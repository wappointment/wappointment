<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Booking;

use Wappointment\Actions\Booking\AssertBookingChangeAllowedAction;
use Wappointment\Actions\Booking\GetAppointmentByKeyAction;
use Wappointment\Actions\Booking\GetAvailableSlotsAction;
use Wappointment\Actions\Booking\GetRescheduleInputAction;
use Wappointment\Http\Requests\GetAvailabilityRequestData;
use Wappointment\Http\Requests\BookingRequestData;
use Wappointment\Http\Responses\GetAvailabilityResponseData;
use Wappointment\System\Optional;

class GetAvailabilityUseCase
{
    public function __construct(
        private GetAvailableSlotsAction $availability,
        private GetAppointmentByKeyAction $getAppointment,
        private AssertBookingChangeAllowedAction $assertChangeAllowed,
        private GetRescheduleInputAction $rescheduleInput
    ) {}

    public function execute(GetAvailabilityRequestData $input): GetAvailabilityResponseData
    {
        if ($input->edit_key instanceof Optional) {
            return GetAvailabilityResponseData::from($this->availability->handle(BookingRequestData::from($input->toArray())));
        }
        $appointment = $this->getAppointment->handle($input->edit_key);
        $this->assertChangeAllowed->handle($appointment, 'reschedule');
        return GetAvailabilityResponseData::from($this->availability->handle(
            $this->rescheduleInput->handle($appointment, $input->date),
            (int) $appointment['id'],
            (int) ($appointment['options']['buffer_time'] ?? 0)
        ));
    }
}
