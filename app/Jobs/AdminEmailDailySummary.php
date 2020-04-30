<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Queue;

class AdminEmailDailySummary extends AbstractEmailJob
{
    const CONTENT = '\\Wappointment\\Messages\\AdminDailySummaryEmail';

    public function afterHandled()
    {
        Queue::queueDailyJob();
    }
}
