<?php

namespace Wappointment\Messages;

class AppointmentEmailFiller extends AbstractEmail
{
    use HasAppointmentFooterLinks;

    protected function loadEmail($subject, $body)
    {
        $this->subject = $subject;
        $this->body = $body;
    }
}
