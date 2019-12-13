<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Client;
use Wappointment\Services\Appointment;
use Wappointment\Validators\HttpRequest\Booking;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\ClassConnect\Request;

class BookingController extends RestController
{
    public function save(Booking $booking)
    {
        if ($booking->hasErrors()) {
            return WPHelpers::restError('Review your fields', 500, $booking->getErrors());
        }
        $result = Client::book($booking);
        if (isset($result['errors'])) {
            return WPHelpers::restError('Impossible to proceed with the booking', 500, $result['errors']);
        }

        return ['result' => true, 'appointment' => (new \Wappointment\ClassConnect\Collection($result->toArraySpecial()))->except(['id', 'client_id'])];
    }

    public function reschedule(Request $request)
    {
        $result = Appointment::reschedule($request->input('appointmentkey'), $request->input('time'));


        return $result;
    }
}
