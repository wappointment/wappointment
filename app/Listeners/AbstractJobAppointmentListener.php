<?php

namespace Wappointment\Listeners;

use Wappointment\Services\Queue;

abstract class AbstractJobAppointmentListener extends AbstractJobRecordListener
{

    protected $jobClass = '';
    protected $cancel = false;
    protected $is_reminder = false;

    protected function addToJobs($event)
    {
        if (empty($this->jobClass)) {
            throw new \WappointmentException('jobClass not defined', 1);
        }

        $this->recordJob(
            $this->jobClass,
            [
                'appointment' => $event->getAppointment(),
                'client' => $event->getClient(),
                'args' => $event->getAdditional(),
            ],
            'client',
            $this->is_reminder ? $event->getAppointment()->id : null
        );
    }

    protected function cancelRelatedAppointmentJob($event)
    {
        Queue::cancelAppointmentJob($event->getAppointment()->id);
    }

    public function handle($event)
    {
        if ($this->cancel) {
            $this->cancelRelatedAppointmentJob($event);
        }

        $this->addToJobs($event);

        $this->queueJobs();
    }
}
