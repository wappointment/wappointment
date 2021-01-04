<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Settings;

trait IsAdminAppointmentJob
{
    public function handle()
    {
        $email_send = $this->prepareEmailSend();
        if ($email_send === false || !$this->transport->send($email_send)) {
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
        if (empty($this->appointment)) {
            return false;
        }
        $email_staff = (new \Wappointment\WP\Staff($this->appointment->getStaffId()))->emailAddress();
        if (!in_array($email_staff, $notifications_emails)) {
            $this->transport->to($email_staff);
        }

        return $this->generateContent();
    }
}
