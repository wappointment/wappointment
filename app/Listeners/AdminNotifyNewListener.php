<?php

namespace Wappointment\Listeners;

class AdminNotifyNewListener extends AbstractJobRecordListener
{
    protected $jobClass = '\Wappointment\Jobs\AdminEmailNewAppointment';

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
