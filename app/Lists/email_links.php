<?php

return [
    [
        'model' => 'appointment',
        'key' => 'linkAddEventToCalendar',
        'label' => __('Link to save appointment to calendar', 'wappointment'),
        'getMethod' => 'getLinkAddEventToCalendar'
    ],
    [
        'model' => 'appointment',
        'key' => 'linkRescheduleEvent',
        'label' => __('Link to reschedule appointment', 'wappointment'),
        'getMethod' => 'getLinkRescheduleEvent'
    ],
    [
        'model' => 'appointment',
        'key' => 'linkCancelEvent',
        'label' => __('Link to cancel appointment', 'wappointment'),
        'getMethod' => 'getLinkCancelEvent'
    ],
    [
        'model' => 'appointment',
        'key' => 'linkNew',
        'label' => __('Link to book a new appointment', 'wappointment'),
        'getMethod' => 'getLinkNewEvent',
        'modelCall' => 'email_helper'
    ],
    [
        'model' => 'appointment',
        'key' => 'linkNewStaff',
        'label' => __('Link to book a new appointment with the same staff', 'wappointment'),
        'getMethod' => 'getLinkNewEventStaff',
        'modelCall' => 'email_helper',
        'requiresParams' => true
    ],
    [
        'model' => 'appointment',
        'key' => 'linkView',
        'label' => __('Link to view the appointment details (Meeting room url etc...)', 'wappointment'),
        'getMethod' => 'getLinkViewEvent'
    ],

];
