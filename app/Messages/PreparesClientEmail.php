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
        $email = $this->tryToLoadEmail($eventType, $this->client->options['locale'] ?? false, $reminderId);

        if (!$email) {
            return false;
        }

        $this->subject = $email->subject;

        $this->body = $email->getHtmlBody($appointment);
        return true;
    }

    private function tryToLoadEmail($eventType, $localized = false, $reminderId)
    {
        if ($localized!==false) {
            $email = $this->tryEmail($eventType, $localized, $reminderId);
            if ($email) {
                return $email;
            }
        }

        return $this->tryEmail($eventType, false, $reminderId);
    }

    private function tryEmail($eventType, $localized = false, $reminderId)
    {
        $query = Reminder::where('published', 1)
        ->where('type', Reminder::getType('email'))
        ->where('event', $eventType);
        if ($localized) {
            $query->where('lang', $localized);
        }
        if ($reminderId>0) {
            $query->where('id', $reminderId);
        }
        return $query->first();
    }
}
