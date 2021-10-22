<?php

namespace Wappointment\Messages;

use Wappointment\WP\Helpers as WPHelpers;

class AdminPendingAppointmentEmail extends AbstractAdminEmail
{
    use AdminGeneratesDefault;
    protected $client = null;
    protected $appointment = null;

    public function loadContent()
    {
        $this->subject = __('Pending appointment', 'wappointment');
        $this->addLogo();
        $this->addBr();

        $buttonConfirm = apply_filters(
            'wappointment_appointment_pending_email_button',
            true,
            $this->params['appointment']->options
        );

        $lines = [
            sprintf(__('Hi %s,', 'wappointment'), $this->params['appointment']->getStaff()->getFirstName()),
            __('A new appointment is pending!', 'wappointment'),
        ];

        if ($buttonConfirm === true) {
            $lines[] = __('Please confirm the appointment.', 'wappointment');
        }

        $this->addLines($lines);

        $this->addRoundedSquare($this->getEmailContent($this->params['client'], $this->params['appointment']));

        $this->addBr();
        $formatString = 'Y-m-d\T00:00:00';

        $st = $this->params['appointment']->start_at->startOfWeek()->format($formatString);
        $end = $this->params['appointment']->start_at->addDays(7)->format($formatString);

        if ($buttonConfirm === true) {
            $tz = $this->getStaffTz($this->params['appointment']);
            $this->addButton(
                __('Confirm appointment', 'wappointment'),
                WPHelpers::adminUrl('wappointment_calendar&start=' .
                    $st . '&end=' . $end . '&timezone=' . $tz . '&open_confirm=' . (int) $this->params['appointment']->id)
            );
        } else {
            $this->addLines($buttonConfirm);
        }
        $this->addLines([
            __('Have a great day!', 'wappointment')
        ]);
    }
}
