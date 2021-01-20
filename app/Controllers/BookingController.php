<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Client;
use Wappointment\Services\Appointment;
use Wappointment\Services\Settings;
use Wappointment\Validators\HttpRequest\Booking;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\ClassConnect\Request;
use Wappointment\Services\DateTime;

class BookingController extends RestController
{
    public function save(Booking $booking)
    {
        if ($booking->hasErrors()) {
            return WPHelpers::restError('Review your fields', 500, $booking->getErrors());
        }

        $appointment = Client::book($booking);
        if (isset($appointment['errors'])) {
            return WPHelpers::restError('Impossible to proceed with the booking', 500, $appointment['errors']);
        }

        $appointmentArray = $appointment->toArraySpecial();

        if (Settings::get('allow_rescheduling')) {
            $appointmentArray['canRescheduleUntil'] = $appointment->canRescheduleUntilTimestamp();
        }
        if (Settings::get('allow_cancellation')) {
            $appointmentArray['canCancelUntil'] = $appointment->canCancelUntilTimestamp();
        }
        return [
            'appointment' => (new \Wappointment\ClassConnect\Collection($appointmentArray))->except(['rest_route', 'id', 'client_id'])

        ];
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
