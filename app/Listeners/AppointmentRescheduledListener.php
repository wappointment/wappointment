<?php

namespace Wappointment\Listeners;

use Wappointment\Models\Reminder;

class AppointmentRescheduledListener extends AppointmentConfirmedListener
{
    protected $jobClass = '\Wappointment\Jobs\AppointmentEmailRescheduled';
    protected $cancel = true;
    protected $event_trigger = Reminder::APPOINTMENT_RESCHEDULED;

    protected function addToJobs($event)
    {
        $this->data_job = [
            'oldAppointment' => $event->getAppointment(),
        ];
        parent::addToJobs($event);
    }
}
