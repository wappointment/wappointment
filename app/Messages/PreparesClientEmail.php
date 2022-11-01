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

        $email = $this->tryToLoadEmail($eventType, $this->client->options['locale'] ?? false);

        if (!$email) {
            return false;
        }

        $this->subject = $email->subject;

        $this->body = $email->getHtmlBody($appointment);
        return true;
    }

    private function tryToLoadEmail($eventType, $localized = false)
    {
        if($localized!==false){
            $email = $this->tryEmail($eventType, $localized);
            if($email){
                return $email;
            }
        }
        
        return $this->tryEmail($eventType);
    }

    private function tryEmail($eventType, $localized = false)
    {
        $query = Reminder::where('published', 1)
        ->where('type', Reminder::getType('email'))
        ->where('event', $eventType);
        if($localized){
            $query->where('lang', $localized);
        }
        return $query->first();
    }
    
    
}
