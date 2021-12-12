<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Client;
use Wappointment\Validators\HttpRequest\Booking;
use Wappointment\Validators\HttpRequest\BookingAdmin;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Admin;
use Wappointment\Services\AppointmentNew as Appointment;
use Wappointment\ClassConnect\Request;
use Wappointment\Formatters\BookingResult;
use Wappointment\Services\DateTime;

class BookingController extends RestController
{
    protected function fieldsError($booking)
    {
        return WPHelpers::restError(__('Review your fields', 'wappointment'), 500, $booking->getErrors());
    }

    protected function bookingFailed()
    {
        return __('Booking failed', 'wappointment');
    }

    public function save(Booking $booking)
    {

        if ($booking->hasErrors()) {
            return $this->fieldsError($booking);
        }

        $result = Client::book($booking);
        if (isset($result['appointment']['errors'])) {
            return WPHelpers::restError($this->bookingFailed(), 500, $result['appointment']['errors']);
        }
        $result['result'] = true;

        return BookingResult::format($result);
    }

    public function adminBook(BookingAdmin $booking)
    {
        if ($booking->hasErrors()) {
            return $this->fieldsError($booking);
        }

        $result = Admin::book($booking);
        if (isset($result['errors'])) {
            return WPHelpers::restError($this->bookingFailed(), 500, $result['errors']);
        }

        return ['message' => __('Appointment recorded', 'wappointment')];
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
