<?php

namespace Wappointment\Listeners;

use Wappointment\Services\Settings;

class AppointmentRescheduledListener extends AbstractJobAppointmentListener
{
    protected $jobClass = '\Wappointment\Jobs\AppointmentEmailRescheduled';

    protected function cancelPendingJobs($event)
    {
        $this->cancelRelatedAppointmentJob($event);
    }

    protected function queueAdminNotification($event)
    {
        if (Settings::get('notify_rescheduled_appointments')) {
            $this->recordJob(
                '\Wappointment\Jobs\AdminEmailRescheduledAppointment',
                [
                    'appointment' => $event->getAppointment(),
                    'client' => $event->getClient(),
                    'oldAppointment' => $event->getOldAppointment()
                ],
                'admin'
            );
        }
    }
}
