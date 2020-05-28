<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;
use Wappointment\Models\Reminder;

class AppointmentReminderEmail extends AbstractEmail
{
    use HasAppointmentFooterLinks, HasTagsToReplace, AttachesIcs, PreparesClientEmail;

    protected $client = null;
    protected $appointment = null;
    protected $icsRequired = true;

    const EVENT = Reminder::APPOINTMENT_STARTS;

    public function loadContent(Client $client, Appointment $appointment, $reminder_id = false)
    {
        if ($reminder_id) {
            if (!$this->prepareClientEmail($client, $appointment, static::EVENT)) {
                return false;
            }

            $this->attachIcs([$appointment], 'appointment');
        }
    }
}
