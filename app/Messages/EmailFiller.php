<?php

namespace Wappointment\Messages;

class EmailFiller extends AbstractEmail
{
    protected function loadEmail($subject = '', $body = '')
    {
        $this->subject = $subject;
        $this->body = $body;
    }
}
