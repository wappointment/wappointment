<?php
declare(strict_types=1);

namespace Wappointment\Actions\Booking;

use Wappointment\Booking\BookingException;
use Wappointment\Http\Requests\CreateBookingRequestData;
use Wappointment\System\Optional;
use Wappointment\System\Settings;

class ValidateBookingContactAction
{
    public function __construct(private Settings $settings) {}

    public function handle(CreateBookingRequestData $input): array
    {
        $name = sanitize_text_field(trim($input->name));
        $email = $this->settings->get('forceemail') && is_user_logged_in()
            ? wp_get_current_user()->user_email
            : ($input->email instanceof Optional ? '' : sanitize_text_field(trim($input->email)));
        if ($name === '' || !is_email($email)) {
            throw new BookingException(__('Enter your name and a valid email address.', 'wappointment'));
        }
        $phone = $input->phone instanceof Optional ? '' : sanitize_text_field(trim($input->phone));
        if ($phone !== '' && !preg_match('/^\+?[\d ()\-.]{5,40}$/D', $phone)) {
            throw new BookingException(__('Enter a valid phone number.', 'wappointment'));
        }
        return compact('name', 'email', 'phone');
    }
}
