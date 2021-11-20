<?php

namespace Wappointment\Models;

use Wappointment\Services\AppointmentNew as AppointmentService;
use Wappointment\Models\Service;

trait CanBook
{

    public $bookingRequest = null;

    public function loadService($bookingRequest, $forceConfirmed)
    {
        $service = Service::find((int)$bookingRequest->get('service'));
        if (empty($service)) {
            throw new \WappointmentException(__('Error cannot load the service', 'wappointment'), 1);
        }
        return $service;
    }

    public function book($bookingRequest, $forceConfirmed = false)
    {
        $this->bookingRequest = $bookingRequest;
        $service = $this->loadService($bookingRequest, $forceConfirmed);

        if (!$bookingRequest->get('service')) {
            return $this->bookLegacy($bookingRequest, $forceConfirmed); //legacy trick for older version of wappo-woo
        }

        if (!empty($this->bookingRequest->get('appointment_key'))) {
            $booked_externally =  apply_filters('wappointment_book_external', false, $this, $service);
            if ($booked_externally === false) {
                throw new \WappointmentException(__('Error while booking', 'wappointment') . '(1)', 1);
            }
            return $booked_externally; //contains the returned data
        }
        $start_at = $bookingRequest->get('time');

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
            throw new \WappointmentException(__('Error while booking', 'wappointment') . '(2)', 1);
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
            throw new \WappointmentException(__('Error while booking', 'wappointment') . '(3)', 1);
        }
        return $hasBeenBooked;
    }

    public function bookNoOrder()
    {
        $this->generatingOrder = false;
    }
}
