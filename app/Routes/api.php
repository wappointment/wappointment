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
        'name' => 'jobs.list',
        'method' => 'GET',
        'path' => '/jobs',
        'controller' => JobsController::class,
        'cacheable' => false,
    ],
    [
        'name' => 'clients.list',
        'method' => 'GET',
        'path' => '/clients',
        'controller' => ListClientsController::class,
        'cacheable' => false,
    ],
    [
        'name' => 'clients.create',
        'method' => 'POST',
        'path' => '/clients',
        'controller' => CreateClientController::class,
        'cacheable' => false,
    ],
    [
        'name' => 'clients.update',
        'method' => 'PUT',
        'path' => '/clients/(?P<id>\d+)',
        'controller' => UpdateClientController::class,
        'cacheable' => false,
    ],
    [
        'name' => 'clients.delete',
        'method' => 'DELETE',
        'path' => '/clients/(?P<id>\d+)',
        'controller' => DeleteClientController::class,
        'cacheable' => false,
    ],
    [
        'name' => 'settings.list',
        'method' => 'GET',
        'path' => '/settings',
        'controller' => GetSettingsController::class,
        'cacheable' => false,
    ],
    [
        'name' => 'settings.save',
        'method' => 'POST',
        'path' => '/settings',
        'controller' => SaveSettingsController::class,
        'cacheable' => false,
    ],
    [
        'name' => 'settings.get',
        'method' => 'GET',
        'path' => '/settings/(?P<name>[a-zA-Z0-9_-]+)',
        'controller' => GetSettingController::class,
        'cacheable' => false,
    ],
];
