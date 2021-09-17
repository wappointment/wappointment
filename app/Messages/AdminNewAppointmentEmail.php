<?php

namespace Wappointment\Messages;

class AdminNewAppointmentEmail extends AbstractAdminEmail
{
    use AttachesIcs, AdminGeneratesDefault;

    public function loadContent()
    {
        $this->subject = __('New appointment', 'wappointment');
        $this->addLogo();
        $this->addBr();

        $this->addLines([
            /* translators: %s is replaced with the first name of the staff being booked */
            sprintf(__('Hi %s', 'wappointment'), $this->params['appointment']->getStaff()->getFirstName()),
            __('Great news! You just got booked!', 'wappointment')
        ]);


        $this->addRoundedSquare($this->getEmailContent($this->params['client'], $this->params['appointment']));

        $this->addLines([
            'Have a great day!',
            '',
        ]);

        if (!$this->areAttachmentsDisabled()) {
            $this->addLines([
                'Ps: An .ics file with the appointment\'s details is attached'
            ]);
        }

        $this->attachIcs([$this->params['appointment']], 'appointment', true);
    }
}
