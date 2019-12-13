<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Services\Client;
use Wappointment\Validators\HttpRequest\BookingAdmin;
use Wappointment\Services\Admin;
use Wappointment\WP\Helpers as WPHelpers;

class ClientController extends RestController
{
    public function search(Request $request)
    {
        return Client::search($request->input('email'));
    }

    public function book(BookingAdmin $booking)
    {
        if ($booking->hasErrors()) {
            return WPHelpers::restError('Review your fields', 500, $booking->getErrors());
        }

        $result = Admin::book($booking);
        if (isset($result['errors'])) {
            return WPHelpers::restError('Impossible to proceed with the booking', 500, $result['errors']);
        }

        return ['message' => 'Appointment recorded'];
    }
}
