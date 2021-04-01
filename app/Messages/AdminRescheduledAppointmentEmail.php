<?php

namespace Wappointment\Messages;

use Wappointment\Services\Settings;

class AdminRescheduledAppointmentEmail extends AbstractAdminEmail
{
    use AttachesIcs, AdminGeneratesDefault;

    public function loadContent()
    {
        $this->subject = 'Rescheduled appointment';
        $this->addLogo();
        $this->addBr();
        $tz = $this->getStaffTz($this->params['appointment']);

        $this->addLines([
            'Hi ' . $this->params['appointment']->getStaff()->getFirstName() . ', ',
            'A client rescheduled his appointment, find the details below.',
        ]);

        $this->addRoundedSquare($this->getEmailContent($this->params['client'], $this->params['appointment']));

        $this->addRoundedSquare(
            [
                '<u>Former appointment</u>',
                'Date: ' . $this->params['oldAppointment']->start_at->setTimezone($tz)->format(Settings::get('date_format')),
                'Time: ' . $this->params['oldAppointment']->start_at->setTimezone($tz)->format(Settings::get('time_format'))
                    . ' - ' . $this->params['oldAppointment']->end_at->setTimezone($tz)->format(Settings::get('time_format')),
            ]
        );
        $this->addLines([
            'Have a great day!',
            '',
            'Ps: An .ics file with the appointment\'s details is attached'
        ]);
        $this->attachIcs([$this->params['appointment']], 'appointment', true);
    }
}
