<?php

namespace Wappointment\Listeners;

class AppointmentBookedListener extends AbstractJobAppointmentListener
{
    protected $jobClass = '\Wappointment\Jobs\AppointmentEmailPending';
}
