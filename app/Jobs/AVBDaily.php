<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Queue;
use Wappointment\Services\Regenerate;

class AVBDaily implements JobInterface
{
    public function handle()
    {
        Regenerate::all();
        Queue::queueRefreshAVBJob();
    }
}
