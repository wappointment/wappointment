<?php

namespace Wappointment\Jobs;

class AppointmentEmailPending extends AppointmentEmailConfirmed
{
    const EMAIL = '\\Wappointment\\Messages\\AppointmentPendingEmail';
}
