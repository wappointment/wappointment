<?php

/**
 * This file has been created for documentation purpose only
 * simply copy and modify the code for your own needs
 * in your active theme's functions.php for instance
 */
if (!defined('WAPPO_NEVER_DEFINED')) {
    exit;
}

/**
 * function triggered when an appointment is confirmed or pending
 *
 * @param Object $appointment (Eloquent model object)
 * @param Object $client (Eloquent model object)
 * @param String $event_type
 * @return void
 */
function my_function_wappointment_appointment_booked($appointment, $client, $event_type)
{
    /**
     * getting an array view of the $appointment and  $client objects
     * $appointment_array = $appointment->toArray();
     * $client_array = $client->toArray();
     */
    if ($event_type == 'appointment.confirmed') { // confirmed booking
        // write your code here ..
    }

    if ($event_type == 'appointment.booked') { // pending booking
        // write your code here for an appointment which is pending..
    }
}
add_action('wappointment_appointment_booked', 'my_function_wappointment_appointment_booked', 10, 3);
