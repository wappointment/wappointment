<?php

namespace Wappointment\Jobs;

use Wappointment\Models\Appointment;

class AppointmentEmailRescheduled extends AppointmentEmailConfirmed
{
    const CONTENT = '\\Wappointment\\Messages\\AppointmentRescheduledEmail';

    protected function generateContent()
    {
        $emailClass = static::CONTENT;
        $data = [
            'client' => $this->client,
            'appointment' => $this->appointment,
            'oldAppointment' => new Appointment($this->params['oldAppointment'])
        ];
        return new $emailClass($data);
    }
}
