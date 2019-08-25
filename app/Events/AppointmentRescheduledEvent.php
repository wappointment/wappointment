<?php

namespace Wappointment\Events;

class AppointmentRescheduledEvent extends AppointmentBookedEvent
{
    const NAME = 'appointment.rescheduled';
}
