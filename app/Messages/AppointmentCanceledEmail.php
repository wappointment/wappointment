<?php

namespace Wappointment\Messages;

class AppointmentCanceledEmail extends ClientBookingConfirmationEmail
{
    use HasNoAppointmentFooterLinks;
    const EVENT = \Wappointment\Models\Reminder::APPOINTMENT_CANCELLED;
}
