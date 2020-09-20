<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;

class AdminCanceledAppointmentEmail extends AbstractAdminEmail
{
    use AttachesIcs, AdminGeneratesDefault;


    public function loadContent()
    {
        $this->subject = 'Cancelled appointment';
        $this->addLogo();
        $this->addBr();

        $this->addLines([
            'Hi ' . $this->params['appointment']->getStaff()->getFirstName() . ', ',
            'Unfortunately a client cancelled his appointment.'
        ]);

        $this->addRoundedSquare($this->getEmailContent($this->params['client'], $this->params['appointment']));

        $this->addLines([
            'Have a great day!',
            '',
            'Ps: An .ics file with the appointment\'s details is attached'
        ]);

        $this->attachCancelled([$this->params['appointment']], 'cancelled_appointment');
    }
}
