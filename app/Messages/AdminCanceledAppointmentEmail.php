<?php

namespace Wappointment\Messages;

class AdminCanceledAppointmentEmail extends AbstractAdminEmail
{
    use AttachesIcs, AdminGeneratesDefault;

    public function loadContent()
    {
        $this->subject = __('Cancelled appointment', 'wappointment');
        $this->addLogo();
        $this->addBr();

        $this->addLines([
            /* translators: %s - client's first name. */
            sprintf(__('Hi %s,', 'wappointment'), $this->params['appointment']->getStaff()->getFirstName()),
            __('Unfortunately a client cancelled his appointment.', 'wappointment')
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


        $this->attachCancelled([$this->params['appointment']], 'cancelled_appointment');
    }
}
