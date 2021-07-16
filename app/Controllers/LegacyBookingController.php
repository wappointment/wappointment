<?php

namespace Wappointment\Controllers;

use Wappointment\Services\ClientLegacy;
use Wappointment\Services\Settings;
use Wappointment\Validators\HttpRequest\LegacyBooking;
use Wappointment\WP\Helpers as WPHelpers;

class LegacyBookingController extends RestController
{
    public function save(LegacyBooking $booking)
    {
        if ($booking->hasErrors()) {
            return WPHelpers::restError('Review your fields', 500, $booking->getErrors());
        }

        $appointment = ClientLegacy::book($booking);

        if (isset($appointment['errors'])) {
            return WPHelpers::restError('Impossible to proceed with the booking', 500, $appointment['errors']);
        }

        $appointmentArray = $appointment->toArraySpecial();

        // if (Settings::get('allow_rescheduling')) {
        //     $appointmentArray['canRescheduleUntil'] = $appointment->canRescheduleUntilTimestamp();
        // }
        // if (Settings::get('allow_cancellation')) {
        //     $appointmentArray['canCancelUntil'] = $appointment->canCancelUntilTimestamp();
        // }
        return [
            'appointment' => (new \Wappointment\ClassConnect\Collection($appointmentArray))->except(['rest_route', 'id', 'client_id'])

        ];
    }
}
