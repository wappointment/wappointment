<?php
return [
    [
        'model' => 'client',
        'key' => 'name',
        'label' => __('Client\'s name', 'wappointment'),
        'sanitize' => true
    ],
    [
        'model' => 'client',
        'key' => 'email',
        'label' => __('Client\'s email', 'wappointment'),
        'sanitize' => true
    ],
    [
        'model' => 'client',
        'key' => 'phone',
        'label' => __('Client\'s phone', 'wappointment'),
        'getMethod' => 'getPhone',
        'sanitize' => true
    ],
    [
        'model' => 'client',
        'key' => 'skype',
        'label' => __('Client\'s skype', 'wappointment'),
        'getMethod' => 'getSkype',
        'sanitize' => true
    ],
    [
        'model' => 'service',
        'key' => 'name',
        'label' => __('Service name', 'wappointment'),
        'getMethod' => 'getServiceName',
        'sanitize' => true,
        'modelCall' => 'appointment'
    ],
    [
        'model' => 'service',
        'key' => 'address',
        'label' => __('Service address', 'wappointment'),
        'getMethod' => 'getServiceAddress',
        'sanitize' => true,
        'modelCall' => 'appointment'
    ],
    [
        'model' => 'appointment',
        'key' => 'duration',
        'label' => __('Appointment\'s duration', 'wappointment'),
        'getMethod' => 'getDuration'
    ],
    [
        'model' => 'appointment',
        'key' => 'location',
        'label' => __('Appointment\'s location', 'wappointment'),
        'getMethod' => 'getLocation'
    ],
    [
        'model' => 'appointment',
        'key' => 'starts',
        'label' => __('Appointment\'s date and time', 'wappointment'),
        'getMethod' => 'getStartsDayAndTime'
    ],
    [
        'model' => 'staff',
        'key' => 'name',
        'label' => __('Staff\'s Name', 'wappointment'),
        'sanitize' => true,
        'getMethod' => 'getStaffName',
        'modelCall' => 'appointment'
    ],
    [
        'model' => 'order',
        'key' => 'summary',
        'label' => __('Order summary acting as a bill', 'wappointment'),
        'getMethod' => 'getOrderTable',
        'modelCall' => 'email_helper',
        'requiresParams' => true
    ]

];
