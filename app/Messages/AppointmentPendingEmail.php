<?php

namespace Wappointment\Messages;

class AppointmentPendingEmail extends ClientBookingConfirmationEmail
{
    use HasNoAppointmentFooterLinks;
    protected $icsRequired = false;

    public const EVENT = \Wappointment\Models\Reminder::APPOINTMENT_PENDING;
}
