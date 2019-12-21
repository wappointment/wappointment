<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;
use Wappointment\Services\Settings;
use Wappointment\Services\Service;

class AdminCanceledAppointmentEmail extends AbstractAdminEmail
{
    protected $client = null;
    protected $appointment = null;

    public function loadEmail(Client $client, Appointment $appointment, $reminder_id = false)
    {
        $this->subject = 'Cancelled appointment';
        $this->addLogo();
        $this->addBr();
        $tz = Settings::getStaff('timezone');
        $this->addRoundedSquare(
            [
                '<u>Cancelled appointment</u>',
                'Date: ' . $appointment->start_at->setTimezone($tz)->format(Settings::get('date_format')),
                'Time: ' . $appointment->start_at->setTimezone($tz)->format(Settings::get('time_format'))
                    . ' - ' . $appointment->end_at->setTimezone($tz)->format(Settings::get('time_format')),
                'Service: ' . Service::get()['name'],
                "Client's name: " . $client->name,
                "Client's email: " . $client->email,
            ]
        );
    }
}
