<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;
use Wappointment\Models\Reminder;

trait PreparesClientEmail
{
    public function prepareClientEmail(Client $client, Appointment $appointment, $eventType, $reminderId = 0)
    {
        $this->client = $client;
        $this->appointment = $appointment;
        $queryReminder = Reminder::where('published', 1)
            ->where('type', Reminder::getType('email'))
            ->where('event', $eventType);
        if ($reminderId>0) {
            $queryReminder->where('id', $reminderId);
        }
        $email = $queryReminder->first();

        if (!$email) {
            return false;
        }

        $this->subject = $email->subject;

        $this->body = $email->getHtmlBody($appointment);
        return true;
    }
}
