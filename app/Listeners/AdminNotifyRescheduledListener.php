<?php

namespace Wappointment\Listeners;

class AdminNotifyRescheduledListener extends AbstractJobRecordListener
{
    protected $jobClass = '\Wappointment\Jobs\AdminEmailRescheduledAppointment';

    protected function addToJobs($event)
    {
        $this->data_job = [
            'appointment' => $event->getAppointment(),
            'client' => $event->getClient(),
            'oldAppointment' => $event->getOldAppointment(),
            'args' => $event->getAdditional(),
        ];
        parent::addToJobs($event);
    }
}
