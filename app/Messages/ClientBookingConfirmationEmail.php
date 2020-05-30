<?php

namespace Wappointment\Messages;

use Wappointment\Models\Reminder;
use Wappointment\Models\Client;
use Wappointment\Models\Appointment;
use Wappointment\Services\Settings;

class ClientBookingConfirmationEmail extends AbstractEmail
{
    use HasAppointmentFooterLinks, HasTagsToReplace, AttachesIcs, PreparesClientEmail;

    protected $client = null;
    protected $appointment = null;
    protected $icsRequired = true;
    public $test = false;

    const EVENT = Reminder::APPOINTMENT_CONFIRMED;

    public function loadContent(Client $client, Appointment $appointment)
    {

        if (!$this->prepareClientEmail($client, $appointment, static::EVENT)) {
            return false;
        }

        if (!empty($client->options['test_appointment'])) {
            $this->subject = '[TEST_EMAIL]' . $this->subject;
            $this->test = true;
        }

        if ($this->icsRequired) {
            $this->attachIcs([$appointment], 'appointment');
        }
    }
}
