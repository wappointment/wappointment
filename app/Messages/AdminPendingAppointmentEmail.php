<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;
use Wappointment\WP\Helpers as WPHelpers;

class AdminPendingAppointmentEmail extends AbstractAdminEmail
{
    use AdminGeneratesDefault;
    protected $client = null;
    protected $appointment = null;

    public function loadContent(Client $client, Appointment $appointment, $reminder_id = false)
    {
        $this->subject = 'Pending appointment';
        $this->addLogo();
        $this->addBr();


        $this->addLines([
            'Hi ' . $appointment->getStaff()->getFirstName() . ', ',
            'Great news! You just got booked! ',
            'Please confirm the appointment.'
        ]);

        $this->addRoundedSquare($this->getEmailContent($client, $appointment));

        $this->addBr();
        $formatString = 'Y-m-d\T00:00:00';

        $st = $appointment->start_at->startOfWeek()->format($formatString);
        $end = $appointment->start_at->addDays(7)->format($formatString);

        $buttonConfirm = true;

        $buttonConfirm = apply_filters(
            'wappointment_appointment_pending_email_button',
            $buttonConfirm,
            $appointment->options
        );

        if ($buttonConfirm === true) {
            $this->addButton(
                'Confirm appointment',
                WPHelpers::adminUrl('wappointment_calendar&start=' .
                    $st . '&end=' . $end . '&timezone=' . $tz . '&open_confirm=' . (int) $appointment->id)
            );
        } else {
            $this->addLines(sanitize_text_field($buttonConfirm));
        }
        $this->addLines([
            'Have a great day!',
        ]);
    }
}
