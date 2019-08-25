<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Settings;

class AdminEmailPendingAppointment extends AbstractAppointmentEmailJob
{
    const EMAIL = '\\Wappointment\\Messages\\AdminPendingAppointmentEmail';

    public function handle()
    {
        $emailObject = $this->generateEmail();
        $result = $this->mailer
            ->to(Settings::get('email_notifications'))
            ->send($emailObject);

        if (!$result) {
            throw new \WappointmentException('Error while sending email', 1);
        }
    }
}
