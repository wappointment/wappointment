<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Queue;

class AdminEmailWeeklySummary extends AdminEmailDailySummary
{
    const CONTENT = '\\Wappointment\\Messages\\AdminWeeklySummaryEmail';

    public function afterHandled()
    {
        Queue::queueWeeklyJob(!empty($this->params['staff_id']) ? $this->params['staff_id'] : false);
    }
}
