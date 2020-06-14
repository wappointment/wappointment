<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;

class AdminNewAppointmentEmail extends AbstractAdminEmail
{
    use AttachesIcs, AdminGeneratesDefault;

    protected $client = null;
    protected $appointment = null;

    public function loadContent(Client $client, Appointment $appointment, $reminder_id = false)
    {
        $this->subject = 'New appointment';
        $this->addLogo();
        $this->addBr();

        $this->addLines([
            'Hi ' . $appointment->getStaff()->getFirstName() . ', ',
            'Great news! You just got booked! '
        ]);

        $this->addRoundedSquare($this->getEmailContent($client, $appointment));

        $this->addLines([
            'Have a great day!',
            '',
            'Ps: An .ics file with the appointment\'s details is attached'
        ]);

        $this->attachIcs([$appointment], 'appointment', true);
    }
}
