<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Settings;

trait IsAdminAppointmentJob
{
    public function handle()
    {

        if (!$this->transport->send($this->prepareEmailSend())) {
            throw new \WappointmentException('Error while sending email', 1);
        }

        if (method_exists($this, 'afterHandled')) {
            $this->afterHandled();
        }
    }

    protected function prepareEmailSend()
    {
        $notifications_emails = Settings::get('email_notifications');
        foreach ($notifications_emails as $notif_email) {
            $this->transport->to(sanitize_email($notif_email));
        }

        $email_staff = (new \Wappointment\WP\Staff($this->appointment->getStaffId()))->emailAddress();
        if (!in_array($email_staff, $notifications_emails)) {
            $this->transport->to($email_staff);
        }

        return $this->generateContent();
    }
}
