<?php
declare(strict_types=1);

use Wappointment\Controllers\Api\JobsController;
use Wappointment\Controllers\Api\ClientsController;
use Wappointment\Controllers\Api\SettingsController;

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
    [
        'method' => 'GET',
        'path' => '/settings',
        'controller' => SettingsController::class,
        'cacheable' => false,
    ],
    [
        'method' => 'POST',
        'path' => '/settings',
        'controller' => SettingsController::class . '@save',
        'cacheable' => false,
    ],
    [
        'method' => 'GET',
        'path' => '/settings/(?P<name>[a-zA-Z0-9_-]+)',
        'controller' => SettingsController::class . '@getSetting',
        'cacheable' => false,
    ],
];
