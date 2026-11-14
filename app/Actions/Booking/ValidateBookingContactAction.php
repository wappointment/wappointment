<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Booking\BookingException;
use Wappointment\Http\Requests\BookingRequestData;
use Wappointment\System\Settings;

class ValidateBookingContactAction
{
    public function __construct(private Settings $settings) {}

    public function handle(BookingRequestData $input): array
    {
        $name = $input->text('name', 100);
        $email = $this->settings->get('forceemail') && is_user_logged_in()
            ? wp_get_current_user()->user_email
            : $input->text('email', 191);
        if ($name === '' || !is_email($email)) {
            throw new BookingException(__('Enter your name and a valid email address.', 'wappointment'));
        }
        $phone = $input->text('phone', 40, '');
        if ($phone !== '' && !preg_match('/^\+?[\d ()\-.]{5,40}$/D', $phone)) {
            throw new BookingException(__('Enter a valid phone number.', 'wappointment'));
        }
        return compact('name', 'email', 'phone');
    }
}
