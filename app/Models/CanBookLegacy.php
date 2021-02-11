<?php

namespace Wappointment\Models;

use Wappointment\Services\AppointmentLegacy as AppointmentServiceLegacy;
use Wappointment\Services\Service;

trait CanBookLegacy
{
    public function bookLegacy($bookingRequest, $forceConfirmed = false)
    {
        $startTime = $bookingRequest->get('time');
        $type = $bookingRequest->get('type');

        $service = Service::get();

        //test type is allowed
        if (!in_array($type, $service['type'])) {
            throw new \WappointmentException('Error booking type not allowed2', 1);
        }

        $type = (int) call_user_func('Wappointment\Models\Appointment::getType' . ucfirst($type));

        //test that this is bookable
        if ($forceConfirmed) {
            $hasBeenBooked = AppointmentServiceLegacy::adminBook(
                $this,
                $startTime,
                $startTime + $this->getRealDuration($service),
                $type,
                $service
            );
        } else {
            $hasBeenBooked = AppointmentServiceLegacy::tryBook(
                $this,
                $startTime,
                $startTime + $this->getRealDuration($service),
                $type,
                $service
            );
        }



        if (!$hasBeenBooked) {
            throw new \WappointmentException('Error cannot book at this time', 1);
        }
        return $hasBeenBooked;
    }
}
