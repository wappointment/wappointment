<?php

namespace Wappointment\Jobs;

class AdminEmailCanceledAppointment extends AbstractAppointmentEmailJob
{
    use IsAdminAppointmentJob;

    const CONTENT = '\\Wappointment\\Messages\\AdminCanceledAppointmentEmail';
}
