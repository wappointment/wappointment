<?php

namespace Wappointment\Messages;

use Wappointment\Services\Settings;

trait AdminGeneratesDefault
{
    public function getEmailContent($client, $appointment)
    {
        $tz = $this->getStaffTz($appointment);

        $dataEmail = [
            '<u>' . $this->subject . '</u>',
            'Date: ' . $appointment->start_at->setTimezone($tz)->format(Settings::get('date_format')),
            'Time: ' . $appointment->start_at->setTimezone($tz)->format(Settings::get('time_format'))
                . ' - ' . $appointment->end_at->setTimezone($tz)->format(Settings::get('time_format')),
            'Service: ' . $appointment->getServiceName(),
            'Location: ' . $appointment->getLocation(),
            "Client's name: " . sanitize_text_field($client->name),
            "Client's email: " . sanitize_text_field($client->email),
        ];
        if (!empty($client->getPhone())) {
            $dataEmail[] = "Client's phone: " . sanitize_text_field($client->getPhone());
        }
        if (!empty($client->getSkype())) {
            $dataEmail[] = "Client's skype: " . sanitize_text_field($client->getSkype());
        }

        if ($appointment->isZoom()) {
            $dataEmail[] = 'Video meeting: <a href="' . $appointment->getLinkViewEvent() . '" >Begin the meeting</a>';
        }

        return apply_filters('wappointment_admin_email_fields', $dataEmail, $client, $appointment);
    }

    public function getStaffTz($appointment)
    {
        $staff = $appointment->getStaff();
        if (!empty($staff)) {
            $tz = $staff->timezone;
        }
        if (empty($tz)) {
            $tz = 'UTC';
        }
        return $tz;
    }
}
