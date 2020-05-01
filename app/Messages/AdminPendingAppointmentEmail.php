<?php

namespace Wappointment\Messages;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;
use Wappointment\Services\Settings;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Service;

class AdminPendingAppointmentEmail extends AbstractAdminEmail
{
    protected $client = null;
    protected $appointment = null;

    public function loadContent(Client $client, Appointment $appointment, $reminder_id = false)
    {
        $this->subject = 'Pending appointment';
        $this->addLogo();
        $this->addBr();
        $tz = Settings::getStaff('timezone');
        $this->addRoundedSquare(
            [
                'Date: ' . $appointment->start_at->setTimezone($tz)->format(Settings::get('date_format')),
                'Time: ' . $appointment->start_at->setTimezone($tz)->format(Settings::get('time_format'))
                    . ' - ' . $appointment->end_at->setTimezone($tz)->format(Settings::get('time_format')),
                'Service: ' . sanitize_text_field(Service::get()['name']),
                "Client's name: " . sanitize_text_field($client->name),
                "Client's email: " . sanitize_text_field($client->email),
            ]
        );
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
    }
}
