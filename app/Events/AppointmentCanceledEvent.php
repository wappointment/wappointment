<?php

namespace Wappointment\Events;

class AppointmentCanceledEvent extends AppointmentBookedEvent
{
    const NAME = 'appointment.canceled';
}
