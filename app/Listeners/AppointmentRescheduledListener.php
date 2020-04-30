<?php

namespace Wappointment\Listeners;

class AppointmentRescheduledListener extends AbstractJobAppointmentListener
{
    protected $jobClass = '\Wappointment\Jobs\AppointmentEmailRescheduled';
    protected $cancel = true;
}
