<?php

return [
    [
        'model' => 'appointment',
        'key' => 'linkAddEventToCalendar',
        'label' => 'Link to save appointment to calendar',
        'getMethod' => 'getLinkAddEventToCalendar'
    ],
    [
        'model' => 'appointment',
        'key' => 'linkRescheduleEvent',
        'label' => 'Link to reschedule appointment',
        'getMethod' => 'getLinkRescheduleEvent'
    ],
    [
        'model' => 'appointment',
        'key' => 'linkCancelEvent',
        'label' => 'Link to cancel appointment',
        'getMethod' => 'getLinkCancelEvent'
    ],
    [
        'model' => 'appointment',
        'key' => 'linkNew',
        'label' => 'Link to book a new appointment',
        'getMethod' => 'getLinkNewEvent',
        'modelCall' => 'email_helper'
    ],
    [
        'model' => 'appointment',
        'key' => 'linkNewStaff',
        'label' => 'Link to book a new appointment with the same staff',
        'getMethod' => 'getLinkNewEventStaff',
        'modelCall' => 'email_helper',
        'requiresParams' => true
    ],
    [
        'model' => 'appointment',
        'key' => 'linkView',
        'label' => 'Link to view the appointment details (Meeting room url etc ...)',
        'getMethod' => 'getLinkViewEvent'
    ],

];
