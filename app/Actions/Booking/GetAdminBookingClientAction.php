<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Auth\AccessPolicy;
use Wappointment\Booking\BookingException;
use Wappointment\Repositories\BookingRepository;

class GetAdminBookingClientAction
{
    public function __construct(private BookingRepository $repository, private AccessPolicy $access) {}

    public function handle(int $id): array
    {
        $this->access->assert('appointments.book');
        $client = $this->repository->adminBookingClient($id, $this->access->appointmentOwner());
        $email = sanitize_email($client['email']);
        $name = sanitize_text_field(trim($client['name']));
        if ($name === '' || !is_email($email)) {
            throw new BookingException(__('The selected client has invalid contact details.', 'wappointment'), 409);
        }
        return [
            'name' => $name,
            'email' => $email,
            'phone' => sanitize_text_field((string) ($client['options']['phone'] ?? '')),
        ];
    }
}
