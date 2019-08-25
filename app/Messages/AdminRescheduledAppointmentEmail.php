<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;
use Wappointment\Services\Settings;

class AdminRescheduledAppointmentEmail extends AbstractAdminEmail
{
    protected $client = null;
    protected $appointment = null;

    public function loadEmail(Client $client, Appointment $appointment, Appointment $oldAppointment)
    {
        $this->subject = 'Rescheduled appointment';
        $this->addLogo();
        $this->addBr();
        $tz = Settings::getStaff('timezone');
        $this->addRoundedSquare(
            [
                '<u>New appointment</u>',
                'Date: ' . $appointment->start_at->setTimezone($tz)->format(Settings::get('date_format')),
                'Time: ' . $appointment->start_at->setTimezone($tz)->format(Settings::get('time_format')) . ' - ' . $appointment->end_at->setTimezone($tz)->format(Settings::get('time_format')),
                'Service: ' . Settings::get('service')['name'],
                "Client's name: " . $client->name,
                "Client's email: " . $client->email,
            ]
        );
        $this->addRoundedSquare(
            [
                '<u>Former appointment</u>',
                'Date: ' . $oldAppointment->start_at->setTimezone($tz)->format(Settings::get('date_format')),
                'Time: ' . $oldAppointment->start_at->setTimezone($tz)->format(Settings::get('time_format')) . ' - ' . $oldAppointment->end_at->setTimezone($tz)->format(Settings::get('time_format')),
            ]
        );
    }
}
