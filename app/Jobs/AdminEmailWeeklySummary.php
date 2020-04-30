<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Queue;

class AdminEmailWeeklySummary extends AbstractEmailJob
{
    const CONTENT = '\\Wappointment\\Messages\\AdminWeeklySummaryEmail';

    public function afterHandled()
    {
        Queue::queueWeeklyJob();
    }
}
