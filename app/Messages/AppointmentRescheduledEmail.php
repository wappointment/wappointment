<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;
use Wappointment\Models\Reminder;

class AppointmentRescheduledEmail extends AbstractEmail
{
    use HasAppointmentFooterLinks, HasTagsToReplace, AttachesIcs, PreparesClientEmail;

    protected $client = null;
    protected $appointment = null;
    protected $icsRequired = true;

    const EVENT = Reminder::APPOINTMENT_RESCHEDULED;

    public function loadContent(Client $client, Appointment $appointment, Appointment $oldAppointment)
    {
        if (!$this->prepareClientEmail($client, $appointment, static::EVENT)) {
            return false;
        }

        if ($this->icsRequired) {
            $this->attachIcs([$appointment], 'appointment');
        }
    }
}
