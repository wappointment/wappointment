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
        'name' => 'appointments.list',
        'method' => 'GET',
        'path' => '/appointments',
        'controller' => \Wappointment\Controllers\Api\Booking\ListAppointmentsController::class,
        'cacheable' => false,
    ],
    [
        'name' => 'appointments.status',
        'method' => 'POST',
        'path' => '/appointments',
        'controller' => \Wappointment\Controllers\Api\Booking\UpdateAppointmentStatusController::class,
        'cacheable' => false,
    ],
    [
        'name' => 'booking-settings.get',
        'method' => 'GET',
        'path' => '/booking-settings',
        'controller' => \Wappointment\Controllers\Api\Booking\GetBookingSettingsController::class,
        'cacheable' => false,
    ],
    [
        'name' => 'booking-settings.save',
        'method' => 'POST',
        'path' => '/booking-settings',
        'controller' => \Wappointment\Controllers\Api\Booking\SaveBookingSettingsController::class,
        'cacheable' => false,
    ],
    [
        'name' => 'booking.catalog',
        'method' => 'GET',
        'path' => '/booking/catalog',
        'controller' => \Wappointment\Controllers\Api\Booking\CatalogController::class,
        'cacheable' => false,
        'permission_callback' => '__return_true',
    ],
    [
        'name' => 'booking.availability',
        'method' => 'POST',
        'path' => '/booking/availability',
        'controller' => \Wappointment\Controllers\Api\Booking\AvailabilityController::class,
        'cacheable' => false,
        'permission_callback' => '__return_true',
    ],
    [
        'name' => 'booking.create',
        'status' => 201,
        'method' => 'POST',
        'path' => '/booking',
        'controller' => \Wappointment\Controllers\Api\Booking\CreateBookingController::class,
        'cacheable' => false,
        'permission_callback' => '__return_true',
    ],
    [
        'name' => 'booking.manage',
        'method' => 'POST',
        'path' => '/booking/manage',
        'controller' => \Wappointment\Controllers\Api\Booking\ManageBookingController::class,
        'cacheable' => false,
        'permission_callback' => '__return_true',
    ],
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
        'status' => 201,
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
