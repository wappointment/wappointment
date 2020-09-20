<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;

class AdminNewAppointmentEmail extends AbstractAdminEmail
{
    use AttachesIcs, AdminGeneratesDefault;


    public function loadContent()
    {
        $this->subject = 'New appointment';
        $this->addLogo();
        $this->addBr();

        $this->addLines([
            'Hi ' . $this->params['appointment']->getStaff()->getFirstName() . ', ',
            'Great news! You just got booked! '
        ]);

        $this->addRoundedSquare($this->getEmailContent($this->params['client'], $this->params['appointment']));

        $this->addLines([
            'Have a great day!',
            '',
            'Ps: An .ics file with the appointment\'s details is attached'
        ]);

        $this->attachIcs([$this->params['appointment']], 'appointment', true);
    }
}
