<?php

namespace Wappointment\Listeners;

class AdminNotifyCanceledListener extends AbstractJobRecordListener
{
    protected $jobClass = '\Wappointment\Jobs\AdminEmailCanceledAppointment';

    protected function addToJobs($event)
    {
        $appointment = $event->getAppointment();
        if ($appointment->service->isGroup()) {
            return false;
        }
        $this->data_job = [
            'appointment' => $appointment,
            'client' => $event->getClient(),
            'args' => $event->getAdditional(),
        ];
        parent::addToJobs($event);
    }
}
