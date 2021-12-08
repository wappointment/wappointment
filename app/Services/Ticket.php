<?php

namespace Wappointment\Services;

class Ticket
{
    public static function cancel($ticket)
    {
        if (!$ticket->is_participant) {
            //if is standard appointment, cancel it
            AppointmentNew::silentCancel([$ticket->id]);
        }
    }
}
