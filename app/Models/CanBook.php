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

        if (!empty($this->bookingRequest->get('appointment_key'))) {
            $result =  apply_filters('wappointment_book_external', false, $bookingRequest);
            if ($result === false) {
                throw new \WappointmentException(__('Error cannot book at this time', 'wappointment'), 1);
            }
        }
        $start_at = $bookingRequest->get('time');

        if ($bookingRequest->get('service')) {
            $service = Service::find((int)$bookingRequest->get('service'));
            if (empty($service)) {
                throw new \WappointmentException(__('Error cannot load the service', 'wappointment'), 1);
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
            throw new \WappointmentException(__('Error cannot book at this time', 'wappointment'), 1);
        }
        return $hasBeenBooked;
    }

    public function bookAsAdmin($booking)
    {
        $this->bookingRequest = $booking;
        $end = $booking->get('end');

        //test that this is bookable
        $hasBeenBooked = AppointmentService::adminBook($this, $booking->get('start'), $end, 'unused', $booking->getService(), $booking->staff);
        if (!$hasBeenBooked) {
            throw new \WappointmentException(__('Error cannot book at this time', 'wappointment'), 1);
        }
        return $hasBeenBooked;
    }

    public function bookNoOrder()
    {
        $this->generatingOrder = false;
    }
}
