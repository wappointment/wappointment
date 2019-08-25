<?php

namespace Wappointment\Jobs;

class AppointmentEmailRescheduled extends AppointmentEmailConfirmed
{
    const EMAIL = '\\Wappointment\\Messages\\AppointmentRescheduledEmail';
}
