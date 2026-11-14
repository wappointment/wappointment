<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Booking;

use Wappointment\Actions\Booking\ChangeBookingAction;
use Wappointment\Actions\Booking\GetAppointmentByKeyAction;
use Wappointment\Actions\Booking\PresentBookingAction;
use Wappointment\Booking\BookingException;
use Wappointment\Http\Requests\ManageBookingRequestData;
use Wappointment\Http\Responses\ManageBookingResponseData;

class ManageBookingUseCase
{
    public function __construct(
        private GetAppointmentByKeyAction $getAppointment,
        private ChangeBookingAction $changeBooking,
        private PresentBookingAction $presentBooking
    ) {}

    public function execute(ManageBookingRequestData $input): ManageBookingResponseData
    {
        $action = $input->text('action', 20);
        $appointment = match ($action) {
            'view' => $this->getAppointment->handle($input),
            'cancel', 'reschedule' => $this->changeBooking->handle($input, $action),
            default => throw new BookingException(__('Unknown appointment action.', 'wappointment')),
        };
        return new ManageBookingResponseData($this->presentBooking->handle($appointment));
    }
}
