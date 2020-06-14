<?php

namespace Wappointment\Messages;

use Wappointment\Services\Settings;
use Wappointment\Services\Service;

trait AdminGeneratesDefault
{
    public function getEmailContent($client, $appointment)
    {
        $tz = Settings::getStaff('timezone');
        $dataEmail = [
            '<u>' . $this->subject . '</u>',
            'Date: ' . $appointment->start_at->setTimezone($tz)->format(Settings::get('date_format')),
            'Time: ' . $appointment->start_at->setTimezone($tz)->format(Settings::get('time_format'))
                . ' - ' . $appointment->end_at->setTimezone($tz)->format(Settings::get('time_format')),
            'Service: ' . sanitize_text_field(Service::get()['name']),
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
        return $dataEmail;
    }
}
