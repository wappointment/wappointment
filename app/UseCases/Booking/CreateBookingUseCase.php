<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Booking;

use Wappointment\Actions\Booking\CreateBookingAction;
use Wappointment\Actions\Booking\PresentBookingAction;
use Wappointment\Actions\Booking\SendBookingConfirmationAction;
use Wappointment\Actions\Booking\ValidateBookingContactAction;
use Wappointment\Http\Requests\CreateBookingRequestData;
use Wappointment\Http\Responses\CreateBookingResponseData;

class CreateBookingUseCase
{
    public function __construct(
        private ValidateBookingContactAction $validateContact,
        private CreateBookingAction $createBooking,
        private PresentBookingAction $presentBooking,
        private SendBookingConfirmationAction $sendConfirmation
    ) {}

    public function execute(CreateBookingRequestData $input): CreateBookingResponseData
    {
        $contact = $this->validateContact->handle($input);
        $appointment = $this->createBooking->handle($input, $contact);
        $result = $this->presentBooking->handle($appointment);
        $result['notification_sent'] = $this->sendConfirmation->handle($contact['email'], $result);
        return CreateBookingResponseData::from($result);
    }
}
