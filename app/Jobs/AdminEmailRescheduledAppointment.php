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
        $data = [
            'client' => $this->client,
            'appointment' => $this->appointment,
            'oldAppointment' => new Appointment($this->params['oldAppointment'])
        ];
        return new $emailClass($data);
    }
}
