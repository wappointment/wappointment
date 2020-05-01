<?php

namespace Wappointment\Messages;

class AppointmentEmailFiller extends AbstractEmail
{
    use HasAppointmentFooterLinks;

    protected function loadContent($subject, $body)
    {
        $this->subject = $subject;
        $this->body = $body;
    }
}
