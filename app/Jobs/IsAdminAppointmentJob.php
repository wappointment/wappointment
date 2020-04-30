<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Settings;

trait IsAdminAppointmentJob
{
    public function handle()
    {
        $emailObject = $this->generateContent();
        $result = $this->transport
            ->to(sanitize_email(Settings::get('email_notifications')))
            ->send($emailObject);

        if (!$result) {
            throw new \WappointmentException('Error while sending email', 1);
        }

        if (method_exists($this, 'afterHandled')) {
            $this->afterHandled();
        }
    }
}
