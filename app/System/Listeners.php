<?php

namespace Wappointment\System;

use Wappointment\Helpers\Events;

class Listeners
{
    public static function init()
    {
        Events::listens('appointment.booked', 'AppointmentBookedListener');
        Events::listens('appointment.booked', 'AppointmentAdminPendingListener');
        Events::listens('appointment.confirmed', 'AppointmentConfirmedListener');
        Events::listens('appointment.rescheduled', 'AppointmentRescheduledListener');
        Events::listens('appointment.canceled', 'AppointmentCanceledListener');
    }
}
