<?php

namespace Wappointment\Messages;

use Wappointment\Models\Reminder;
use Wappointment\Models\Client;
use Wappointment\Models\Appointment;

class ClientBookingConfirmationEmail extends AbstractEmail
{
    use HasAppointmentFooterLinks, HasTagsToReplace;
    protected $client = null;
    protected $appointment = null;
    const EVENT = Reminder::APPOINTMENT_CONFIRMED;

    public function loadContent(Client $client, Appointment $appointment, $reminder_id = false)
    {
        $this->client = $client;
        $this->appointment = $appointment;

        $email = Reminder::where('published', 1)
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
