<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;
use Wappointment\Models\Reminder;

class AppointmentReminderEmail extends ClientBookingConfirmationEmail
{
    const EVENT = \Wappointment\Models\Reminder::APPOINTMENT_STARTS;

    public function loadEmail(Client $client, Appointment $appointment, $reminder_id = false)
    {
        $this->client = $client;
        $this->appointment = $appointment;

        if ($reminder_id) {
            $email = Reminder::where('id', $reminder_id)
                ->where('published', 1)
                ->where('type', Reminder::getType('email'))
                ->where('event', static::EVENT)
                ->first();
            if (!$email) {
                return;
            }


            $this->subject = $email->subject;
            $this->body = $email->getHtmlBody($appointment);
        }
    }
}
