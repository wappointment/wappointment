<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;
use Wappointment\Services\Settings;
use Wappointment\Services\Service;

class AdminRescheduledAppointmentEmail extends AbstractAdminEmail
{
    use AttachesIcs;

    protected $client = null;
    protected $appointment = null;

    public function loadContent(Client $client, Appointment $appointment, Appointment $oldAppointment)
    {
        $this->subject = 'Rescheduled appointment';
        $this->addLogo();
        $this->addBr();
        $tz = Settings::getStaff('timezone');

        $this->addLines([
            'Hi ' . $appointment->getStaff()->getFirstName() . ', ',
            'A client rescheduled his appointment, find the details below.',
        ]);

        $this->addRoundedSquare(
            [
                '<u>New appointment</u>',
                'Date: ' . $appointment->start_at->setTimezone($tz)->format(Settings::get('date_format')),
                'Time: ' . $appointment->start_at->setTimezone($tz)->format(Settings::get('time_format'))
                    . ' - ' . $appointment->end_at->setTimezone($tz)->format(Settings::get('time_format')),
                'Service: ' . Service::get()['name'],
                "Client's name: " . $client->name,
                "Client's email: " . $client->email,
            ]
        );
        $this->addRoundedSquare(
            [
                '<u>Former appointment</u>',
                'Date: ' . $oldAppointment->start_at->setTimezone($tz)->format(Settings::get('date_format')),
                'Time: ' . $oldAppointment->start_at->setTimezone($tz)->format(Settings::get('time_format'))
                    . ' - ' . $oldAppointment->end_at->setTimezone($tz)->format(Settings::get('time_format')),
            ]
        );
        $this->addLines([
            'Have a great day!',
            '',
            'Ps: An .ics file with the appointment\'s details is attached'
        ]);
        $this->attachIcs([$appointment], 'appointment', true);
        //$this->attachCancelled([$oldAppointment], 'cancelled_appointment', true);
    }
}
