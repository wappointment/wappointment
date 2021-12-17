<?php

namespace Wappointment\Events;

use Wappointment\Models\Reminder;
use Wappointment\Services\Wappointment\DotCom;
use Wappointment\Services\Settings;

class AppointmentBookedEvent extends AbstractEvent
{
    const NAME = 'appointment.booked';

    protected $appointment;
    protected $client;
    protected $oldAppointment;
    protected $reminders;
    protected $bagArgs;
    protected $order;

    public function __construct($args)
    {
        $this->bagArgs = $args;
        $this->client = $args['client'];
        $this->appointment = $args['appointment'];
        $this->appointment->setSharedClient($this->client);

        if (!empty($args['oldAppointment'])) {
            $this->oldAppointment = $args['oldAppointment'];
        }
        if (!empty($args['order'])) {
            $this->order = $args['order'];
        }
        $this->reminders = Reminder::select('id', 'event', 'type', 'options')
            ->where('published', 1)
            ->whereIn('type', Reminder::getTypes('code'))
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

    public function getOrder()
    {
        return $this->order;
    }

    public function getArgs()
    {
        return $this->bagArgs;
    }

    public function getAdditional()
    {
        return apply_filters('wappointment_appointment_job_requires_args', false, $this);
    }
}
