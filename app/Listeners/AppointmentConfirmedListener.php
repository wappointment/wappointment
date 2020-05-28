<?php

namespace Wappointment\Listeners;

use Wappointment\Models\Reminder;

class AppointmentConfirmedListener extends AbstractJobAppointmentListener
{
    protected $jobClass = '\Wappointment\Jobs\AppointmentEmailConfirmed';
    protected $delay = 0;
    protected $event_trigger = Reminder::APPOINTMENT_CONFIRMED;

    protected function addToJobs($event)
    {

        $params = [
            'appointment' => $event->getAppointment(),
            'client' => $event->getClient(),
        ];

        foreach ($event->getReminders() as $reminder) {
            if ($reminder->type == Reminder::getType('email') && $reminder->event == $this->event_trigger) {
                $params['reminder_id'] =  0;

                $this->recordJob(
                    $this->jobClass,
                    array_merge($params, $this->data_job),
                    'client',
                    null,
                    $this->delay
                );
            }
        }
    }
}
