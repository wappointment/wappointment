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

    public static function cancelTrigger($ticket, $slots = false)
    {
        do_action('wappointment_cancel_ticket', $ticket, $slots);
    }
}
