<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Settings;

class AdminEmailNewAppointment extends AbstractAppointmentEmailJob
{
    const EMAIL = '\\Wappointment\\Messages\\AdminNewAppointmentEmail';

    public function handle()
    {
        $emailObject = $this->generateEmail();

        $result = $this->mailer
            ->to(sanitize_email(Settings::get('email_notifications')))
            ->send($emailObject);

        if (!$result) {
            throw new \WappointmentException('Error while sending email', 1);
        }
    }
}
