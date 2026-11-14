<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Booking;

use Wappointment\Actions\Booking\AssertBookingChangeAllowedAction;
use Wappointment\Actions\Booking\GetAppointmentByKeyAction;
use Wappointment\Actions\Booking\GetAvailableSlotsAction;
use Wappointment\Actions\Booking\GetRescheduleInputAction;
use Wappointment\Http\Requests\GetAvailabilityRequestData;
use Wappointment\Http\Responses\GetAvailabilityResponseData;

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
        if (empty($input->toArray()['edit_key'])) {
            return new GetAvailabilityResponseData($this->availability->handle($input));
        }
        $appointment = $this->getAppointment->handle($input);
        $this->assertChangeAllowed->handle($appointment, 'reschedule');
        return new GetAvailabilityResponseData($this->availability->handle(
            $this->rescheduleInput->handle($appointment, $input),
            (int) $appointment['id'],
            (int) ($appointment['options']['buffer_time'] ?? 0)
        ));
    }
}
