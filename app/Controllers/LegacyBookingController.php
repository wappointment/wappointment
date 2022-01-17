<?php

namespace Wappointment\Controllers;

use Wappointment\Helpers\Translations;
use Wappointment\Services\ClientLegacy;
use Wappointment\Services\Settings;
use Wappointment\Validators\HttpRequest\LegacyBooking;
use Wappointment\WP\Helpers as WPHelpers;

class LegacyBookingController extends RestController
{
    public function save(LegacyBooking $booking)
    {
        if ($booking->hasErrors()) {
            return WPHelpers::restError(Translations::get('review_fields'), 500, $booking->getErrors());
        }

        $appointment = ClientLegacy::book($booking);

        if (isset($appointment['errors'])) {
            return WPHelpers::restError(Translations::get('booking_failed'), 500, $appointment['errors']);
        }

        $appointmentArray = $appointment->toArraySpecial();

        return [
            'appointment' => (new \Wappointment\ClassConnect\Collection($appointmentArray))->except(['rest_route', 'id', 'client_id'])

        ];
    }
}
