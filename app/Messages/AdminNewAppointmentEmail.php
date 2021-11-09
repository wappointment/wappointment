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
            __('Have a great day!', 'wappointment'),
            '',
        ]);

        if (!$this->areAttachmentsDisabled()) {
            $this->addLines([
                __('Ps: An .ics file with the appointment\'s details is attached', 'wappointment')
            ]);
        }

        $this->attachIcs([$this->params['appointment']], 'appointment', true);
    }
}
