<?php

namespace Wappointment\Listeners;

class AdminNotifyRescheduledListener extends AbstractJobRecordListener
{
    protected $jobClass = '\Wappointment\Jobs\AdminEmailRescheduledAppointment';

    protected function addToJobs($event)
    {
        $appointment = $event->getAppointment();
        if ($appointment->service->isGroup()) {
            return false;
        }
        $this->data_job = [
            'appointment' => $appointment,
            'client' => $event->getClient(),
            'oldAppointment' => $event->getOldAppointment(),
            'args' => $event->getAdditional(),
        ];
        parent::addToJobs($event);
    }
}
