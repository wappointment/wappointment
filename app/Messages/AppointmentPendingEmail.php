<?php

namespace Wappointment\Messages;

class AppointmentPendingEmail extends ClientBookingConfirmationEmail
{
    use HasNoAppointmentFooterLinks;

    const EVENT = \Wappointment\Models\Reminder::APPOINTMENT_PENDING;
}
