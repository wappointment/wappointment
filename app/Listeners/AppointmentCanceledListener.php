<?php

namespace Wappointment\Listeners;

class AppointmentCanceledListener extends AbstractJobAppointmentListener
{
    protected $jobClass = '\Wappointment\Jobs\AppointmentEmailCanceled';
    protected $cancel = true;
}
