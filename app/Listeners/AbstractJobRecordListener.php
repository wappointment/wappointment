<?php

namespace Wappointment\Listeners;

use Wappointment\Events\AppointmentBookedEvent;
use Wappointment\Services\Queue;
use Wappointment\Services\Settings;

abstract class AbstractJobRecordListener
{
    protected $jobs = [];

    protected function cancelPendingJobs($event)
    {
    }

    protected function queueConfirmationEmail($event)
    {
    }

    protected function queueAdminNotification($event)
    {
    }

    public function handle(AppointmentBookedEvent $event)
    {

        $this->cancelPendingJobs($event);

        // there is only something to queue if email is configured
        if ((bool)Settings::get('mail_status')) {
            $this->queueConfirmationEmail($event);

            $this->queueAdminNotification($event);

            $this->queueJobs();
        }
    }

    protected function recordJob($jobClass, $params, $queue = 'default', $appointment_id = 0, $available_at = 0)
    {
        $this->jobs[] = [
            'payload' => json_encode([
                'job' => $jobClass,
                'params' => $params
            ]),
            'queue' => $queue,
            'appointment_id' => $appointment_id,
            'created_at' => time(),
            'available_at' => $available_at
        ];
    }

    private function queueJobs()
    {
        if (!empty($this->jobs)) {
            Queue::pushMultiple($this->jobs);
        }
    }
}
