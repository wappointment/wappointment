<?php

namespace Wappointment\Listeners;

use Wappointment\Services\Settings;

class AppointmentCanceledListener extends AbstractJobAppointmentListener
{
    protected $jobClass = '\Wappointment\Jobs\AppointmentEmailCanceled';

    protected function cancelPendingJobs($event)
    {
        $this->cancelRelatedAppointmentJob($event);
    }

    protected function queueAdminNotification($event)
    {
        if (Settings::get('notify_canceled_appointments')) {
            $this->recordJob(
                '\Wappointment\Jobs\AdminEmailCanceledAppointment',
                [
                    'appointment' => $event->getAppointment(),
                    'client' => $event->getClient()
                ],
                'admin'
            );
        }
    }
}
