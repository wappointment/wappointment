<?php

namespace Wappointment\Listeners;

use Wappointment\Services\Queue;

abstract class AbstractJobRecordListener
{
    protected $jobs = [];
    protected $admin = false;
    protected $data_job  = [];

    protected function addToJobs($event)
    {
        if (empty($this->jobClass)) {
            throw new \WappointmentException('jobClass not defined', 1);
        }

        $this->recordJob(
            $this->jobClass,
            $this->data_job,
            $this->admin ? 'admin' : 'client',
            null
        );
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

    protected function queueJobs()
    {
        if (!empty($this->jobs)) {
            Queue::pushMultiple($this->jobs);
        }
    }

    public function handle($event)
    {

        $this->addToJobs($event);

        $this->queueJobs();
    }
}
