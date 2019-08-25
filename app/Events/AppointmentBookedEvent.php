<?php

namespace Wappointment\Events;

class AppointmentBookedEvent extends AbstractEvent
{
    const NAME = 'appointment.booked';

    protected $appointment;
    protected $client;
    protected $oldAppointment;

    public function __construct($args)
    {
        $this->appointment = $args['appointment'];
        $this->client = $args['client'];
        if (!empty($args['oldAppointment'])) {
            $this->oldAppointment = $args['oldAppointment'];
        }
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getAppointment()
    {
        return $this->appointment;
    }

    public function getOldAppointment()
    {
        return $this->oldAppointment;
    }
}
