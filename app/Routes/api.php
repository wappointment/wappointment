<?php
declare(strict_types=1);

use Wappointment\Controllers\Api\JobsController;
use Wappointment\Controllers\Api\Clients\ListClientsController;
use Wappointment\Controllers\Api\Clients\CreateClientController;
use Wappointment\Controllers\Api\Clients\UpdateClientController;
use Wappointment\Controllers\Api\Clients\DeleteClientController;
use Wappointment\Controllers\Api\Settings\GetSettingsController;
use Wappointment\Controllers\Api\Settings\SaveSettingsController;
use Wappointment\Controllers\Api\Settings\GetSettingController;

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
        'controller' => ListClientsController::class,
        'cacheable' => false,
    ],
    [
        'method' => 'POST',
        'path' => '/clients',
        'controller' => CreateClientController::class,
        'cacheable' => false,
    ],
    [
        'method' => 'POST',
        'path' => '/clients/(?P<id>\d+)',
        'controller' => UpdateClientController::class,
        'cacheable' => false,
    ],
    [
        'method' => 'POST',
        'path' => '/clients/delete/(?P<id>\d+)',
        'controller' => DeleteClientController::class,
        'cacheable' => false,
    ],
    [
        'method' => 'GET',
        'path' => '/settings',
        'controller' => GetSettingsController::class,
        'cacheable' => false,
    ],
    [
        'method' => 'POST',
        'path' => '/settings',
        'controller' => SaveSettingsController::class,
        'cacheable' => false,
    ],
    [
        'method' => 'GET',
        'path' => '/settings/(?P<name>[a-zA-Z0-9_-]+)',
        'controller' => GetSettingController::class,
        'cacheable' => false,
    ],
];
