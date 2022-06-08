<?php

namespace Wappointment\Messages;

use Wappointment\Services\Addons;
use Wappointment\Services\Settings;

trait AdminGeneratesDefault
{
    public function getEmailContent($client, $appointment)
    {
        $tz = $this->getStaffTz($appointment);
        $date_start = $appointment->start_at->setTimezone($tz)->format(Settings::get('date_format'));
        $time_start = $appointment->start_at->setTimezone($tz)->format(Settings::get('time_format'));
        $end_copy = $appointment->end_at->copy();

        if (!empty($appointment->options['buffer_time'])) {
            $end_copy->subMinutes($appointment->options['buffer_time']);
        }

        $time_end = $end_copy->setTimezone($tz)->format(Settings::get('time_format'));
        $dataEmail = [
            '<u>' . $this->subject . '</u>',
            /* translators: %s is replaced with the date the appointment startt at */
            sprintf(__('Date: %s', 'wappointment'), $date_start),
            /* translators: %1$s is replaced with the start time, %2$s is replaced with the end time  */
            sprintf(__('Time: %1$s - %2$s', 'wappointment'), $time_start, $time_end),
            /* translators: %s is replaced with the staff name */
            sprintf(__('Staff: %s', 'wappointment'), $appointment->getStaffName()),
            /* translators: %s is replaced with the service name */
            sprintf(__('Service: %s', 'wappointment'), $appointment->getServiceName()),
            /* translators: %s is replaced with the location name */
            sprintf(__('Location: %s', 'wappointment'), $appointment->getLocation()),
            /* translators: %s is replaced with the client's name */
            sprintf(__("Client's name: %s", 'wappointment'), sanitize_text_field($client->name)),
            /* translators: %s is replaced with the client's email */
            sprintf(__("Client's email: %s", 'wappointment'), sanitize_text_field($client->email)),
        ];

        if (!Addons::isActive('wappointment_services')) {
            if (!empty($client->getPhone())) {
                /* translators: %s is replaced with the client's phone */
                $dataEmail[] =  sprintf(__("Client's phone: %s", 'wappointment'), sanitize_text_field($client->getPhone()));
            }
            if (!empty($client->getSkype())) {
                /* translators: %s is replaced with the client's skype username */
                $dataEmail[] =  sprintf(__("Client's skype: %s", 'wappointment'), sanitize_text_field($client->getSkype()));
            }
        }


        if ($appointment->isZoom()) {
            /* translators: %s is replaced with a "Begin the meeting" button linking to a wappointment page */
            $dataEmail[] =  sprintf(__("Video meeting: %s", 'wappointment'), '<a href="' . $appointment->getLinkViewEvent() . '" >' . __('Begin meeting', 'wappointment') . '</a>');
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
            $tz = Settings::getStaff('timezone', 'UTC');
        }
        return $tz;
    }
}
