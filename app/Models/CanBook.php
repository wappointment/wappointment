<?php

namespace Wappointment\Models;

use Wappointment\Services\AppointmentNew as AppointmentService;
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
        } else {
            return $this->bookLegacy($bookingRequest, $forceConfirmed); //legacy trick for older version of wappo-woo
        }

        $duration = $service->hasDuration($bookingRequest->get('duration'));
        $end_at = $start_at + $this->getRealDuration(['duration' => $duration]);

        $staff = !empty($bookingRequest->staff) ? $bookingRequest->staff : $bookingRequest->get('staff');
        if ($forceConfirmed) {
            $hasBeenBooked = AppointmentService::adminBook($this, $start_at, $end_at, false, $service, $staff);
        } else {
            $hasBeenBooked = AppointmentService::tryBook($this, $start_at, $end_at, false, $service, $staff);
        }

        //test that this is bookable
        if (!$hasBeenBooked) {
            throw new \WappointmentException('Error cannot book at this time', 1);
        }
        return $hasBeenBooked;
    }

    public function bookAsAdmin($booking)
    {
        $this->bookingRequest = $booking;
        $end = $booking->get('end');

        $staff_id = empty($booking->get('staff_id')) ? null : $booking->get('staff_id');
        //test that this is bookable
        $hasBeenBooked = AppointmentService::adminBook($this, $booking->get('start'), $end, 'unused', $booking->getService(), $booking->staff);
        if (!$hasBeenBooked) {
            throw new \WappointmentException('Error cannot book at this time', 1);
        }
        return $hasBeenBooked;
    }
}
