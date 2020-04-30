<?php

namespace Wappointment\Jobs;

class AdminEmailPendingAppointment extends AbstractAppointmentEmailJob
{
    use IsAdminAppointmentJob;

    const CONTENT = '\\Wappointment\\Messages\\AdminPendingAppointmentEmail';
}
