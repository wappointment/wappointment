<?php

namespace Wappointment\Messages;

use Wappointment\Services\Settings;

class AdminRescheduledAppointmentEmail extends AbstractAdminEmail
{
    use AttachesIcs, AdminGeneratesDefault;

    public function loadContent()
    {
        $this->subject = __('Rescheduled appointment', 'wappointment');
        $this->addLogo();
        $this->addBr();
        $tz = $this->getStaffTz($this->params['appointment']);

        $this->addLines([
            sprintf(__('Hi %s,', 'wappointment'), $this->params['appointment']->getStaff()->getFirstName()),
            __('A client rescheduled his appointment, find the details below.', 'wappointment'),
        ]);

        $this->addRoundedSquare($this->getEmailContent($this->params['client'], $this->params['appointment']));

        $this->addRoundedSquare(
            [
                '<u>' . __('Former appointment', 'wappointment') . '</u>',
                'Date: ' . $this->params['oldAppointment']->start_at->setTimezone($tz)->format(Settings::get('date_format')),
                'Time: ' . $this->params['oldAppointment']->start_at->setTimezone($tz)->format(Settings::get('time_format'))
                    . ' - ' . $this->params['oldAppointment']->end_at->setTimezone($tz)->format(Settings::get('time_format')),
            ]
        );
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
