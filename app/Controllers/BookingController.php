<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Client;
use Wappointment\Validators\HttpRequest\Booking;
use Wappointment\Validators\HttpRequest\BookingAdmin;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Admin;
use Wappointment\Services\Settings;
use Wappointment\Models\Appointment as AppointmentModel;
use Wappointment\Services\Appointment;
use Wappointment\ClassConnect\Request;
use Wappointment\Services\DateTime;

class BookingController extends RestController
{
    // public function fetch($request)
    // {
    //     $appointment = AppointmentModel::with('service')
    //         ->where('status', '>=', AppointmentModel::STATUS_AWAITING_CONFIRMATION)
    //         ->where('edit_key', $request->input('appointmentkey'))
    //         ->first();

    //     if (empty($appointment)) {
    //         throw new \WappointmentException("Can't find appointment", 1);
    //     }
    //     $appointmentData = $appointment->toArraySpecial();

    //     if (Settings::get('allow_rescheduling')) {
    //         $appointmentData['canRescheduleUntil'] = $appointment->canRescheduleUntilTimestamp();
    //     }
    //     if (Settings::get('allow_cancellation')) {
    //         $appointmentData['canCancelUntil'] = $appointment->canCancelUntilTimestamp();
    //     }
    //     return [
    //         'appointment' => $appointmentData,
    //         'client' => $appointment->client()->select(['name', 'email', 'options'])->first(),
    //         'service' => empty($appointmentData['service']) ? \Wappointment\Services\Service::get() : $appointmentData['service'],
    //         'staff' => (new \Wappointment\WP\Staff($appointment->getStaffId()))->toArray(),
    //         'date_format' => Settings::get('date_format'),
    //         'time_format' => Settings::get('time_format'),
    //         'date_time_union' => Settings::get('date_time_union', ' - '),
    //     ];
    // }
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
