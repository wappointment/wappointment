<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Queue;
use Wappointment\Services\Settings;

class AdminEmailDailySummary extends AbstractEmailJob
{
    const CONTENT = '\\Wappointment\\Messages\\AdminDailySummaryEmail';

    public function afterHandled()
    {
        Queue::queueDailyJob(!empty($this->params['staff_id']) ? $this->params['staff_id'] : false);
    }

    protected function prepareEmailSend()
    {
        $notifications_emails = Settings::get('email_notifications');
        foreach ($notifications_emails as $notif_email) {
            $this->transport->to(sanitize_email($notif_email));
        }

        if ($this->isLegacy()) {
            $email_staff = (new \Wappointment\WP\StaffLegacy())->emailAddress();
        } else {
            $email_staff = (new \Wappointment\WP\Staff($this->params['staff_id']))->emailAddress();
        }

        if (!in_array($email_staff, $notifications_emails)) {
            $this->transport->to($email_staff);
        }

        return $this->generateContent();
    }
}
