<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Settings;
use Wappointment\Models\Appointment;

class AdminEmailRescheduledAppointment extends AbstractAppointmentEmailJob
{
    const EMAIL = '\\Wappointment\\Messages\\AdminRescheduledAppointmentEmail';

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

    protected function generateEmail()
    {
        $emailClass = static::EMAIL;
        return new $emailClass($this->client, $this->appointment, new Appointment($this->params['oldAppointment']));
    }
}
