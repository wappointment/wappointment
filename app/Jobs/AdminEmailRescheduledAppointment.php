<?php

namespace Wappointment\Jobs;

use Wappointment\Models\Appointment;

class AdminEmailRescheduledAppointment extends AbstractAppointmentEmailJob
{
    use IsAdminAppointmentJob;

    const CONTENT = '\\Wappointment\\Messages\\AdminRescheduledAppointmentEmail';

    protected function generateContent()
    {
        $emailClass = static::CONTENT;
        return new $emailClass($this->client, $this->appointment, new Appointment($this->params['oldAppointment']));
    }
}
