<?php

namespace Wappointment\Listeners;

class AdminNotifyCanceledListener extends AbstractJobRecordListener
{
    protected $jobClass = '\Wappointment\Jobs\AdminEmailCanceledAppointment';

    protected function addToJobs($event)
    {
        $this->data_job = [
            'appointment' => $event->getAppointment(),
            'client' => $event->getClient(),
            'args' => $event->getAdditional(),
        ];
        parent::addToJobs($event);
    }
}
