<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;

class AdminCanceledAppointmentEmail extends AbstractAdminEmail
{
    use AttachesIcs, AdminGeneratesDefault;

    protected $client = null;
    protected $appointment = null;

    public function loadContent(Client $client, Appointment $appointment, $reminder_id = false)
    {
        $this->subject = 'Cancelled appointment';
        $this->addLogo();
        $this->addBr();


        $this->addLines([
            'Hi ' . $appointment->getStaff()->getFirstName() . ', ',
            'Unfortunately a client cancelled his appointment.'
        ]);

        $this->addRoundedSquare($this->getEmailContent($client, $appointment));

        $this->addLines([
            'Have a great day!',
            '',
            'Ps: An .ics file with the appointment\'s details is attached'
        ]);

        $this->attachCancelled([$appointment], 'cancelled_appointment');
    }
}
