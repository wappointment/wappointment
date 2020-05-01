<?php

namespace Wappointment\Listeners;

use Wappointment\Models\Reminder;

class AppointmentCanceledListener extends AppointmentConfirmedListener
{
    protected $jobClass = '\Wappointment\Jobs\AppointmentEmailCanceled';
    protected $cancel = true;
    protected $event_trigger = Reminder::APPOINTMENT_CANCELLED;
}
