<?php

namespace Wappointment\Routes;

class Main extends AbstractRoutes
{
    protected $routes = [
        'public' => [
            'GET' => [
                '/ping' => [
                    'controller' => 'PingController',
                    'method' => 'ping',
                ],
                '/availability' => [
                    'controller' => 'AvailabilityController',
                    'method' => 'get',
                ],
                '/appointment' => [
                    'controller' => 'AppointmentController',
                    'method' => 'get',
                ],
            ],
            'POST' => [
                '/convertdate' => [
                    'controller' => 'BookingController',
                    'method' => 'convertDate',
                ],
                '/services/booking' => [
                    'controller' => 'BookingController',
                    'method' => 'save',
                    'hint' => 'Booking'
                ],
                '/booking' => [
                    'controller' => 'LegacyBookingController',
                    'method' => 'save',
                    'hint' => 'LegacyBooking'
                ],
            ],
            'PATCH' => [
                '/booking' => [
                    'controller' => 'BookingController',
                    'method' => 'reschedule',
                ],
                '/appointment' => [
                    'controller' => 'AppointmentController',
                    'method' => 'cancel',
                ]
            ],
        ],
        'administrator' => [
            'RESOURCE' => [
                '/addons' => [
                    'methods' => ['get', 'post'],
                    'controller' => 'AddonsController'
                ],
                '/events' => [
                    'methods' => ['get', 'post', 'delete', 'patch', 'put'],
                    'controller' => 'EventsController'
                ],
                '/reminder' => [
                    'methods' => ['get', 'post', 'delete', 'patch'],
                    'controller' => 'ReminderController'
                ],

                '/service' => [
                    'methods' => ['post'],
                    'controller' => 'ServiceController'
                ],
                '/status' => [
                    'methods' => ['post', 'delete'],
                    'controller' => 'StatusController'
                ],
                '/services' => [
                    'methods' => ['get', 'post', 'delete'],
                    'controller' => 'ServicesController'
                ],
                '/calendars' => [
                    'methods' => ['get', 'post', 'delete'],
                    'controller' => 'CalendarsController'
                ],
                '/services/location' => [
                    'methods' => ['get', 'post', 'delete'],
                    'controller' => 'LocationsController'
                ],
            ],
            'GET' => [
                '/addons/check' => [
                    'method' => 'check',
                    'controller' => 'AddonsController'
                ],
                '/pingAdmin' => [
                    'controller' => 'PingController',
                    'method' => 'pingAdmin',
                ],
                '/settings/(?P<key>\S+)' => [
                    'controller' => 'SettingsController',
                    'method' => 'get',
                ],
                '/settingsstaff/(?P<key>\S+)' => [
                    'controller' => 'SettingsStaffController',
                    'method' => 'get',
                ],
                '/client' => [
                    'controller' => 'ClientController',
                    'method' => 'index',
                ],

                '/services/custom_fields' => [
                    'method' => 'get',
                    'controller' => 'CustomFieldsController'
                ],
            ],
            'POST' => [
                '/events/record' => [
                    'method' => 'recordDotcom',
                    'controller' => 'EventsController'
                ],
                '/send_feedback' => [
                    'method' => 'sendFeedback',
                    'controller' => 'AppController',
                ],
                '/app/migrate' => [
                    'method' => 'migrate',
                    'controller' => 'AppController',
                ],
                '/wappointment/connect' => [
                    'method' => 'connect',
                    'controller' => 'WappointmentController',
                ],
                '/wappointment/disconnect' => [
                    'method' => 'disconnect',
                    'controller' => 'WappointmentController',
                ],
                '/wappointment/refresh' => [
                    'method' => 'refresh',
                    'controller' => 'WappointmentController',
                ],
                '/wappointment/subscribe' => [
                    'method' => 'subscribe',
                    'controller' => 'WappointmentController',
                    'hint' => 'SubscribeAdmin'
                ],
                '/wappointment/sendtestbooking' => [
                    'method' => 'sendTestBooking',
                    'controller' => 'WappointmentController',
                ],
                '/wappointment/sendignore' => [
                    'method' => 'sendIgnoreBooking',
                    'controller' => 'WappointmentController',
                ],
                '/addons/install' => [
                    'method' => 'install',
                    'controller' => 'AddonsController'
                ],
                '/addons/activate' => [
                    'method' => 'activate',
                    'controller' => 'AddonsController'
                ],
                '/addons/deactivate' => [
                    'method' => 'deactivate',
                    'controller' => 'AddonsController'
                ],

                '/events' => [
                    'method' => 'save',
                    'controller' => 'EventsController',
                    'hint' => 'BookingAdmin'
                ],
                '/freshinstall' => [
                    'controller' => 'DebugController',
                    'method' => 'freshInstall',
                ],
                '/updatepage' => [
                    'controller' => 'DebugController',
                    'method' => 'updatePage',
                ],
                '/settings' => [
                    'controller' => 'SettingsController',
                    'method' => 'save',
                ],
                '/settingsstaff' => [
                    'controller' => 'SettingsStaffController',
                    'method' => 'save',
                ],
                '/settingsstaff/savecal' => [
                    'controller' => 'SettingsStaffController',
                    'method' => 'saveCal',
                ],
                '/settingsstaff/disconnect' => [
                    'controller' => 'SettingsStaffController',
                    'method' => 'disconnectCal',
                ],
                '/settingsstaff/refreshcalendars' => [
                    'controller' => 'SettingsStaffController',
                    'method' => 'refreshCalendars',
                ],

                '/reminderpreview' => [
                    'controller' => 'ReminderController',
                    'method' => 'preview'
                ],
                '/settings/sendtestemail' => [
                    'controller' => 'SettingsController',
                    'method' => 'sendPreviewEmail'
                ],
                '/client/search' => [
                    'method' => 'search',
                    'controller' => 'ClientController'
                ],
                '/client/book' => [
                    'method' => 'book',
                    'controller' => 'ClientController',
                    'hint' => 'BookingAdmin'
                ],
                '/client' => [
                    'method' => 'save',
                    'controller' => 'ClientController'
                ],
                '/client/delete' => [
                    'method' => 'delete',
                    'controller' => 'ClientController'
                ],
                '/services/reorder' => [
                    'controller' => 'ServicesController',
                    'method' => 'reorder',
                ],
                '/calendars/reorder' => [
                    'controller' => 'CalendarsController',
                    'method' => 'reorder',
                ],
                '/services/booking/admin' => [
                    'controller' => 'BookingController',
                    'method' => 'adminBook',
                    'hint' => 'BookingAdmin'
                ],
            ],
        ]
    ];

    public function __construct()
    {
        new Init();
        parent::__construct();
    }
}
