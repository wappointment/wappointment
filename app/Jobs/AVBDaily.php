<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Availability;
use Wappointment\Services\Queue;
use Wappointment\Services\Staff;

class AVBDaily implements JobInterface
{
    public function handle()
    {
        foreach (Staff::get() as $staff) {
            (new Availability($staff['id']))->regenerate();
        }
        Queue::queueRefreshAVBJob();
    }
}
