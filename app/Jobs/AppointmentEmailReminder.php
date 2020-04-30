<?php

namespace Wappointment\Jobs;

class AppointmentEmailReminder extends AppointmentEmailConfirmed
{
    const CONTENT = '\\Wappointment\\Messages\\AppointmentReminderEmail';
}
