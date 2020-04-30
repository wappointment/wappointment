<?php

namespace Wappointment\Jobs;

class AppointmentEmailConfirmed extends AbstractAppointmentEmailJob
{
    const CONTENT = '\\Wappointment\\Messages\\ClientBookingConfirmationEmail';
}
