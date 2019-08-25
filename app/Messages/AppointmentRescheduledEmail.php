<?php

namespace Wappointment\Messages;

class AppointmentRescheduledEmail extends ClientBookingConfirmationEmail
{
    const EVENT = \Wappointment\Models\Reminder::APPOINTMENT_RESCHEDULED;
}
