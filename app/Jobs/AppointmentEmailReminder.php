<?php

namespace Wappointment\Jobs;

class AppointmentEmailReminder extends AppointmentEmailConfirmed
{
    public const CONTENT = '\\Wappointment\\Messages\\AppointmentReminderEmail';
    protected $get_first = false;
}
