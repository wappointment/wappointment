<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;
use Wappointment\Models\Reminder;

trait PreparesClientEmail
{
    public function prepareClientEmail(Client $client, Appointment $appointment, $eventType)
    {
        $this->client = $client;
        $this->appointment = $appointment;

        $email = Reminder::where('published', 1)
            ->where('type', Reminder::getType('email'))
            ->where('event', $eventType)
            ->first();

        if (!$email) {
            return false;
        }

        $this->subject = $email->subject;

        $this->body = $email->getHtmlBody($appointment);
        return true;
    }
}
