<?php

namespace Wappointment\Listeners;

use Wappointment\Models\Reminder;

class AppointmentRescheduledListener extends AppointmentConfirmedListener
{
    protected $jobClass = '\Wappointment\Jobs\AppointmentEmailRescheduled';
    protected $cancel = true;
    protected $event_trigger = Reminder::APPOINTMENT_RESCHEDULED;
}
