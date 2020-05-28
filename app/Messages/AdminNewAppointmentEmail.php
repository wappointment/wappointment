<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;
use Wappointment\Services\Settings;
use Wappointment\Services\Service;

class AdminNewAppointmentEmail extends AbstractAdminEmail
{
    use AttachesIcs;

    protected $client = null;
    protected $appointment = null;

    public function loadContent(Client $client, Appointment $appointment, $reminder_id = false)
    {
        $this->subject = 'New appointment';
        $this->addLogo();
        $this->addBr();
        $tz = Settings::getStaff('timezone');

        $this->addLines([
            'Hi ' . $appointment->getStaff()->getFirstName() . ', ',
            'Great news! You just got booked! '
        ]);

        $this->addRoundedSquare(
            [
                'Date: ' . $appointment->start_at->setTimezone($tz)->format(Settings::get('date_format')),
                'Time: ' . $appointment->start_at->setTimezone($tz)->format(Settings::get('time_format'))
                    . ' - ' . $appointment->end_at->setTimezone($tz)->format(Settings::get('time_format')),
                'Service: ' . sanitize_text_field(Service::get()['name']),
                "Client's name: " . sanitize_text_field($client->name),
                "Client's email: " . sanitize_text_field($client->email),
            ]
        );

        $this->addLines([
            'Have a great day!',
            '',
            'Ps: An .ics file with the appointment\'s details is attached'
        ]);

        $this->attachIcs([$appointment], 'appointment', true);
    }
}
