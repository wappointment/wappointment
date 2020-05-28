<?php

namespace Wappointment\Jobs;

use Wappointment\Models\Appointment;

class AppointmentEmailRescheduled extends AppointmentEmailConfirmed
{
    const CONTENT = '\\Wappointment\\Messages\\AppointmentRescheduledEmail';

    protected function generateContent()
    {
        $emailClass = static::CONTENT;
        return new $emailClass($this->client, $this->appointment, new Appointment($this->params['oldAppointment']));
    }
}
