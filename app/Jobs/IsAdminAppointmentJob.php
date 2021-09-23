<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Settings;
use Wappointment\Services\VersionDB;

trait IsAdminAppointmentJob
{
    public function handle()
    {
        $email_send = $this->prepareEmailSend();
        if ($email_send === false || !$this->transport->send($email_send)) {
            throw new \WappointmentException('Error while sending email 2', 1);
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

        if ($this->isLegacy()) {
            $email_staff = (new \Wappointment\WP\StaffLegacy($this->appointment->getStaffId()))->emailAddress();
        } else {
            $email_staff = (new \Wappointment\WP\Staff($this->appointment->getStaffId()))->emailAddress();
        }

        if (!in_array($email_staff, $notifications_emails)) {
            $this->transport->to($email_staff);
        }

        $this->addReplyTo();

        return $this->generateContent();
    }

    protected function addReplyTo()
    {
        if (!empty($this->appointment)) {
            $this->transport->reply($this->appointment->client->email, $this->appointment->client->name);
        }
    }

    protected function isLegacy()
    {
        return VersionDB::isLessThan(VersionDB::CAN_CREATE_SERVICES);
    }
}
