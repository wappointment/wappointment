<?php

namespace Wappointment\Listeners;

use Wappointment\Services\Queue;

abstract class AbstractJobAppointmentListener extends AbstractJobRecordListener
{
    protected $jobClass = '';

    protected function queueConfirmationEmail($event)
    {
        if (empty($this->jobClass)) {
            throw new \WappointmentException('jobClass not defined', 1);
        }

        $this->recordJob(
            $this->jobClass,
            [
                'appointment' => $event->getAppointment(),
                'client' => $event->getClient()
            ],
            'client',
            $event->getAppointment()->id
        );
    }

    protected function cancelRelatedAppointmentJob($event)
    {
        Queue::cancelAppointmentJob($event->getAppointment()->id);
    }
}
