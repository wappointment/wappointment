<?php

namespace Wappointment\Jobs;

class AppointmentEmailRescheduled extends AppointmentEmailConfirmed
{
    const CONTENT = '\\Wappointment\\Messages\\AppointmentRescheduledEmail';
}
