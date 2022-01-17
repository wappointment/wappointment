<?php

namespace Wappointment\System;

use Wappointment\Helpers\Events;
use Wappointment\Services\Settings;

class Listeners
{
    public static function init()
    {
        if (Settings::get('mail_status')) {
            //pending confirmation event
            Events::listens('appointment.booked', 'AppointmentBookedListener');

            if (Settings::get('notify_pending_appointments')) {
                Events::listens('appointment.booked', 'AppointmentAdminPendingListener');
            }

            //confirmed event
            Events::listens('appointment.confirmed', 'AppointmentConfirmedListener');
            Events::listens('appointment.confirmed', 'AppointmentReminderListener');
            if (Settings::get('notify_new_appointments')) {
                Events::listens('appointment.confirmed', 'AdminNotifyNewListener');
            }

            //rescheduled event
            Events::listens('appointment.rescheduled', 'AppointmentRescheduledListener');
            if (Settings::get('notify_rescheduled_appointments')) {
                Events::listens('appointment.rescheduled', 'AdminNotifyRescheduledListener');
            }

            //canceled event
            Events::listens('appointment.canceled', 'AppointmentCanceledListener');
            if (Settings::get('notify_canceled_appointments')) {
                Events::listens('appointment.canceled', 'AdminNotifyCanceledListener');
            }
        }

        do_action('wappointments_listeners_init');
    }
}
