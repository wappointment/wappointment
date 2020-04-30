<?php

namespace Wappointment\Jobs;

class AppointmentEmailPending extends AppointmentEmailConfirmed
{
    const CONTENT = '\\Wappointment\\Messages\\AppointmentPendingEmail';
}
