<?php
declare(strict_types=1);

use Wappointment\Controllers\Api\JobsController;
use Wappointment\Controllers\Api\ClientsController;

return [
    [
        'method' => 'GET',
        'path' => '/jobs',
        'controller' => JobsController::class,
        'cacheable' => false,
    ],
    [
        'method' => 'GET',
        'path' => '/clients',
        'controller' => ClientsController::class,
        'cacheable' => false,
    ],
    [
        'method' => 'POST',
        'path' => '/clients',
        'controller' => ClientsController::class . '@create',
        'cacheable' => false,
    ],
    [
        'method' => 'PUT',
        'path' => '/clients/(?P<id>\d+)',
        'controller' => ClientsController::class . '@update',
        'cacheable' => false,
    ],
    [
        'method' => 'DELETE',
        'path' => '/clients/(?P<id>\d+)',
        'controller' => ClientsController::class . '@delete',
        'cacheable' => false,
    ],
];
