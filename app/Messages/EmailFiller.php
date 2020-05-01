<?php

namespace Wappointment\Messages;

class EmailFiller extends AbstractEmail
{
    protected function loadContent($subject = '', $body = '')
    {
        $this->subject = $subject;
        $this->body = $body;
    }
}
