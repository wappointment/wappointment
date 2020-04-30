<?php

namespace Wappointment\Events;

use Wappointment\Models\Reminder;

class AppointmentBookedEvent extends AbstractEvent
{
    const NAME = 'appointment.booked';

    protected $appointment;
    protected $client;
    protected $oldAppointment;
    protected $reminders;

    public function __construct($args)
    {
        $this->appointment = $args['appointment'];
        $this->client = $args['client'];
        if (!empty($args['oldAppointment'])) {
            $this->oldAppointment = $args['oldAppointment'];
        }

        $this->reminders = Reminder::select('id', 'event', 'type', 'options')->where('published', 1)
            ->whereIn('event', [Reminder::APPOINTMENT_STARTS, Reminder::APPOINTMENT_CONFIRMED])
            ->get();
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

    public function getReminders()
    {
        return $this->reminders;
    }
}
