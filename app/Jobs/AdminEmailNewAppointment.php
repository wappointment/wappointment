<?php

namespace Wappointment\Jobs;

class AdminEmailNewAppointment extends AbstractAppointmentEmailJob
{
    use IsAdminAppointmentJob;

    const CONTENT = '\\Wappointment\\Messages\\AdminNewAppointmentEmail';
}
