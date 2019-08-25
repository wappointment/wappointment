<?php

namespace Wappointment\Listeners;

class AppointmentAdminPendingListener extends AbstractJobAppointmentListener
{
    protected $jobClass = '\Wappointment\Jobs\AdminEmailPendingAppointment';
}
