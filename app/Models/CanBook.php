<?php

namespace Wappointment\Models;

use Wappointment\Services\Appointment as AppointmentService;
use Wappointment\Models\Service;

trait CanBook
{

    public $bookingRequest = null;

    public function book($bookingRequest, $forceConfirmed = false)
    {
        $this->bookingRequest = $bookingRequest;
        $start_at = $bookingRequest->get('time');

        if ($bookingRequest->get('service')) {
            $service = Service::find((int)$bookingRequest->get('service'));
            if (empty($service)) {
                throw new \WappointmentException("Error cannot load the service", 1);
            }
        }
        $duration = $service->hasDuration($bookingRequest->get('duration'));
        $end_at = $start_at + $this->getRealDuration(['duration' => $duration]);

        if ($forceConfirmed) {
            $hasBeenBooked = AppointmentService::adminBook($this, $start_at, $end_at, false, $service);
        } else {
            $hasBeenBooked = AppointmentService::tryBook($this, $start_at, $end_at, false, $service);
        }

        //test that this is bookable
        //$hasBeenBooked = AppointmentService::tryBook($this, $start_at, $end_at, false, $service);
        if (!$hasBeenBooked) {
            throw new \WappointmentException('Error cannot book at this time', 1);
        }
        return $hasBeenBooked;
    }

    public function bookAsAdmin($booking)
    {
        $this->bookingRequest = $booking;
        $end = $booking->get('end');

        //test that this is bookable
        $hasBeenBooked = AppointmentService::adminBook($this, $booking->get('start'), $end, 'unused', $booking->getService());
        if (!$hasBeenBooked) {
            throw new \WappointmentException('Error cannot book at this time', 1);
        }
        return $hasBeenBooked;
    }
}
