<?php

namespace Wappointment\Messages;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Settings;

class AdminPendingAppointmentEmail extends AbstractAdminEmail
{
    use AdminGeneratesDefault;
    protected $client = null;
    protected $appointment = null;

    public function loadContent()
    {
        $this->subject = 'Pending appointment';
        $this->addLogo();
        $this->addBr();


        $this->addLines([
            'Hi ' . $this->params['appointment']->getStaff()->getFirstName() . ', ',
            'Great news! You just got booked! ',
            'Please confirm the appointment.'
        ]);

        $this->addRoundedSquare($this->getEmailContent($this->params['client'], $this->params['appointment']));

        $this->addBr();
        $formatString = 'Y-m-d\T00:00:00';

        $st = $this->params['appointment']->start_at->startOfWeek()->format($formatString);
        $end = $this->params['appointment']->start_at->addDays(7)->format($formatString);

        $buttonConfirm = true;

        $buttonConfirm = apply_filters(
            'wappointment_appointment_pending_email_button',
            $buttonConfirm,
            $this->params['appointment']->options
        );

        if ($buttonConfirm === true) {
            $tz = Settings::getStaff('timezone');
            $this->addButton(
                'Confirm appointment',
                WPHelpers::adminUrl('wappointment_calendar&start=' .
                    $st . '&end=' . $end . '&timezone=' . $tz . '&open_confirm=' . (int) $this->params['appointment']->id)
            );
        } else {
            $this->addLines(sanitize_text_field($buttonConfirm));
        }
        $this->addLines([
            'Have a great day!',
        ]);
    }
}
