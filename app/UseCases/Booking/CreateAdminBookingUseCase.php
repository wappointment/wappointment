<?php
declare(strict_types=1);

namespace Wappointment\UseCases\Booking;

use Wappointment\Actions\Booking\CreateBookingAction;
use Wappointment\Actions\Booking\GetAdminBookingClientAction;
use Wappointment\Actions\Booking\PresentBookingAction;
use Wappointment\Auth\AccessPolicy;
use Wappointment\Http\Requests\AdminBookingRequestData;
use Wappointment\Http\Requests\CreateBookingRequestData;
use Wappointment\Http\Responses\CreateAdminBookingResponseData;

class CreateAdminBookingUseCase
{
    public function __construct(
        private GetAdminBookingClientAction $getClient,
        private CreateBookingAction $createBooking,
        private PresentBookingAction $presentBooking,
        private AccessPolicy $access
    ) {}

    public function execute(AdminBookingRequestData $requestData): CreateAdminBookingResponseData
    {
        $contact = $this->getClient->handle($requestData->client_id);
        $input = CreateBookingRequestData::from([
            'service_id' => $requestData->service_id,
            'calendar_id' => $requestData->calendar_id,
            'location_id' => $requestData->location_id,
            'duration' => $requestData->duration,
            'date' => $requestData->date,
            'start' => $requestData->start,
            'name' => $contact['name'],
            'email' => $contact['email'],
            'phone' => $contact['phone'],
        ]);
        $appointment = $this->createBooking->handle($input, $contact, $this->access->appointmentOwner());
        $result = $this->presentBooking->handle($appointment);
        $result['notification_queued'] = $appointment['notification_queued'];
        return CreateAdminBookingResponseData::from($result);
    }
}
