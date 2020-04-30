<?php

namespace Wappointment\Jobs;

class AppointmentEmailCanceled extends AppointmentEmailConfirmed
{
    const CONTENT = '\\Wappointment\\Messages\\AppointmentCanceledEmail';
}
