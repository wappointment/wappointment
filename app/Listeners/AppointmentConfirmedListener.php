<?php

namespace Wappointment\Listeners;

use Wappointment\Models\Reminder;

class AppointmentConfirmedListener extends AbstractJobAppointmentListener
{
    protected $jobClass = '\Wappointment\Jobs\AppointmentEmailConfirmed';
    protected $delay = 0;
    protected function addToJobs($event)
    {

        $params = [
            'appointment' => $event->getAppointment(),
            'client' => $event->getClient(),
        ];

        foreach ($event->getReminders() as $reminder) {
            if ($reminder->type == Reminder::getType('email') && $reminder->event == Reminder::APPOINTMENT_CONFIRMED) {
                $params['reminder_id'] =  0;

                $this->recordJob(
                    $this->jobClass,
                    $params,
                    'client',
                    !empty($params['appointment']->id) ? $params['appointment']->id : null,
                    $this->delay
                );
            }
        }
    }
}
