<?php

/**
 * This file has been created for documentation purpose only
 * simply copy and modify the code for your own needs
 * in your active theme's functions.php for instance
 */

/**
 * functions triggered when an appointment is pending, confirmed, canceled and rescheduled
 *
 * @param Object $eventObject
 *                  $client = $eventObject->getClient(); // $client (Eloquent model object)
 *                  $appointment = $eventObject->getAppointment(); // $appointment (Eloquent model object)
 *                  $service = $eventObject->getService(); // $service (Eloquent model object)
 * @return void
 */
function my_function_wappointment_appointment_booked($eventObject)
{
    // reached when appointment is booked and is pending
    $client = $eventObject->getClient();
    $appointment = $eventObject->getAppointment();
    $phoneNumber = $client->getPhone();
    $serviceName = $appointment->getServiceName();
    $duration = $appointment->getDuration();
    $startAndDayTime = $appointment->getStartsDayAndTime();
}
add_action('wappointment_appointment_booked', 'my_function_wappointment_appointment_booked', 10, 1);

function my_function_wappointment_appointment_confirmed($eventObject)
{
    // reached when appointment is confirmed
}
add_action('wappointment_appointment_confirmed', 'my_function_wappointment_appointment_confirmed', 10, 1);

function my_function_wappointment_appointment_canceled($eventObject)
{
    // reached when appointment is canceled
}
add_action('wappointment_appointment_canceled', 'my_function_wappointment_appointment_canceled', 10, 1);


function my_function_wappointment_appointment_rescheduled($eventObject)
{
    // reached when appointment is rescheduled
}
add_action('wappointment_appointment_rescheduled', 'my_function_wappointment_appointment_rescheduled', 10, 1);
