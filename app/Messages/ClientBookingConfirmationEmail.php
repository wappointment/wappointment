<?php

namespace Wappointment\Messages;

use Wappointment\Models\Reminder;
use Wappointment\Models\Client;
use Wappointment\Models\Appointment;

class ClientBookingConfirmationEmail extends AbstractEmail
{
    use HasAppointmentFooterLinks, HasTagsToReplace, AttachesIcs, PreparesClientEmail;

    protected $client = null;
    protected $appointment = null;
    protected $icsRequired = true;

    const EVENT = Reminder::APPOINTMENT_CONFIRMED;

    public function loadContent(Client $client, Appointment $appointment)
    {

        if (!$this->prepareClientEmail($client, $appointment, static::EVENT)) {
            return false;
        }

        if ($this->icsRequired) {
            $this->attachIcs([$appointment], 'appointment');
        }
    }
}
