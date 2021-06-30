<?php

return [
    [
        'model' => 'client',
        'key' => 'name',
        'label' => 'Client\'s name',
        'sanitize' => true
    ],
    [
        'model' => 'client',
        'key' => 'email',
        'label' => 'Client\'s email',
        'sanitize' => true
    ],
    [
        'model' => 'client',
        'key' => 'phone',
        'label' => 'Client\'s phone',
        'getMethod' => 'getPhone',
        'sanitize' => true
    ],
    [
        'model' => 'client',
        'key' => 'skype',
        'label' => 'Client\'s skype',
        'getMethod' => 'getSkype',
        'sanitize' => true
    ],
    [
        'model' => 'service',
        'key' => 'name',
        'label' => 'Service name',
        'getMethod' => 'getServiceName',
        'sanitize' => true,
        'modelCall' => 'appointment'
    ],
    [
        'model' => 'service',
        'key' => 'address',
        'label' => 'Service address',
        'getMethod' => 'getServiceAddress',
        'sanitize' => true,
        'modelCall' => 'appointment'
    ],
    [
        'model' => 'appointment',
        'key' => 'duration',
        'label' => 'Appointment\'s duration',
        'getMethod' => 'getDuration'
    ],
    [
        'model' => 'appointment',
        'key' => 'location',
        'label' => 'Appointment\'s location',
        'getMethod' => 'getLocation'
    ],
    [
        'model' => 'appointment',
        'key' => 'starts',
        'label' => 'Appointment\'s date and time',
        'getMethod' => 'getStartsDayAndTime'
    ],
    [
        'model' => 'staff',
        'key' => 'name',
        'label' => 'Staff Name',
        'getMethod' => 'getStaffName',
        'modelCall' => 'appointment'
    ],
    [
        'model' => 'order',
        'key' => 'summary',
        'label' => 'Display an order summary table for the ',
        'getMethod' => 'getOrderTable',
        'modelCall' => 'email_helper',
        'requiresParams' => true
    ]
];
