<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Client;
use Wappointment\Validators\HttpRequest\Booking;
use Wappointment\Validators\HttpRequest\BookingAdmin;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Admin;
use Wappointment\Services\AppointmentNew as Appointment;
use Wappointment\ClassConnect\Request;
use Wappointment\Services\DateTime;

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

    public function adminBook(BookingAdmin $booking)
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

    public function reschedule(Request $request)
    {
        return Appointment::reschedule($request->input('appointmentkey'), $request->input('time'));
    }

    public function convertDate(Request $request)
    {
        return [
            'converted' => $this->convert((int) $request->input('timestamp'), $request->input('timezone'))
        ];
    }

    protected function convert($ts, $tz)
    {
        return DateTime::i18nDateTime(
            $ts,
            $tz
        );
    }
}
