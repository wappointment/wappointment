<?php

namespace Wappointment\Jobs;

class AppointmentEmailCanceled extends AppointmentEmailConfirmed
{
    const EMAIL = '\\Wappointment\\Messages\\AppointmentCanceledEmail';
}
