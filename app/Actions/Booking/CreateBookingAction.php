<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Booking\BookingException;
use Wappointment\Http\Requests\BookingRequestData;
use Wappointment\Repositories\BookingRepository;
use Wappointment\System\Settings;

class CreateBookingAction
{
    public function __construct(
        private BookingRepository $repository,
        private ValidateBookingSelectionAction $selection,
        private GetSelectedSlotAction $selectedSlot,
        private Settings $settings
    ) {}

    public function handle(BookingRequestData $input, array $contact): array
    {
        return $this->repository->exclusive(function () use ($input, $contact): array {
            [$service, $calendar, $location] = $this->selection->handle($input);
            $fields = array_merge($service['options']['fields'] ?? [], $location['options']['fields'] ?? []);
            if ((in_array('phone', $fields, true) || (int) $location['type'] === 2) && $contact['phone'] === '') {
                throw new BookingException(__('A phone number is required for this appointment.', 'wappointment'));
            }
            $slot = $this->selectedSlot->handle($input);
            $client = $this->repository->client($contact);
            $limit = (int) $this->settings->get('max_active_bookings', 0);
            if ($limit > 0 && $this->repository->activeBookings(
                $client, $this->settings->get('max_active_per_staff') ? (int) $calendar['id'] : null, time()
            ) >= $limit) {
                throw new BookingException(__('You have reached the maximum number of active appointments.', 'wappointment'), 409);
            }
            $data = [
                'start_at' => gmdate('Y-m-d H:i:s', $slot['start']),
                'end_at' => gmdate('Y-m-d H:i:s', $slot['reserved_until']),
                'type' => (int) $location['type'] > 3 ? (int) $location['type'] : (int) $location['type'] - 1,
                'status' => (int) $this->settings->get('approval_mode', 1) === 1 ? 1 : 0,
                'edit_key' => bin2hex(random_bytes(16)),
                'client_id' => $client, 'staff_id' => (int) $calendar['id'],
                'service_id' => (int) $service['id'], 'location_id' => (int) $location['id'],
                'options' => wp_json_encode([
                    'buffer_time' => ($slot['reserved_until'] - $slot['end']) / 60,
                    'duration' => ($slot['end'] - $slot['start']) / 60,
                    'booking_contact' => $contact,
                ]),
                'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
            ];
            $data['id'] = $this->repository->insert('appointments', $data);
            return $data;
        });
    }
}
