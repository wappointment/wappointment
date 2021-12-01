<?php

namespace Wappointment\Services;

class Ticket
{
    public static function cancel($ticket)
    {
        if (!$ticket->is_participant) {
            //if is group appointment, load and cancel ticket
            AppointmentNew::silentCancel($ticket);
        }
    }
}
