<?php

namespace Wappointment\Jobs;

class AppointmentEmailConfirmed extends AbstractAppointmentEmailJob
{
    const EMAIL = '\\Wappointment\\Messages\\ClientBookingConfirmationEmail';

    public function handle()
    {
        $emailObject = $this->generateEmail();
        //if the email is empty that means there was no result so we can just skip sending the email
        if (!empty($emailObject->body)) {
            $result = $this->mailer
                ->to($this->client->mailableAddress())
                ->send($emailObject);

            if (!$result) {
                throw new \WappointmentException('Error while sending email', 1);
            }
        }
    }
}
