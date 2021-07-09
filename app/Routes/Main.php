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
                '/reminder' => [
                    'methods' => ['get', 'post', 'delete', 'patch'],
                    'controller' => 'ReminderController'
                ],

                '/service' => [
                    'methods' => ['post'],
                    'controller' => 'ServiceController'
                ],
                '/services' => [
                    'methods' => ['post', 'delete'],
                    'controller' => 'ServicesController'
                ],
                '/calendars' => [
                    'methods' => ['delete'],
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

                '/services/custom_fields' => [
                    'method' => 'get',
                    'controller' => 'CustomFieldsController'
                ],
                '/services' => [
                    'method' => 'get',
                    'controller' => 'ServicesController',
                    'paginated' => true
                ],

            ],
            'POST' => [
                '/addons/clear' => [
                    'method' => 'clear',
                    'controller' => 'AddonsController'
                ],
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
                '/wappointment/refresh' => [
                    'method' => 'refresh',
                    'controller' => 'CalendarsController',
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
                'refreshcache' => [
                    'controller' => 'DebugController',
                    'method' => 'refreshCache',
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

                '/services/reorder' => [
                    'controller' => 'ServicesController',
                    'method' => 'reorder',
                ],
                '/calendars/reorder' => [
                    'controller' => 'CalendarsController',
                    'method' => 'reorder',
                ],
                '/calendars/permissions' => [
                    'controller' => 'CalendarsController',
                    'method' => 'savePermissions',
                ],
            ],
        ],
        'mixed' => [
            'GET' => [
                '/calendars' => [
                    'method' => 'get',
                    'controller' => 'CalendarsController',
                    'cap' => 'wappo_self_man'
                ],
                '/config/calendar' => [
                    'controller' => 'ViewsDataController',
                    'method' => 'getCalendar',
                    'cap' => 'wappo_calendar_man'
                ],
                '/client' => [
                    'controller' => 'ClientController',
                    'method' => 'index',
                    'cap' => 'wappo_clients_man'
                ],
            ],
            'POST' => [
                '/events/delete' => [
                    'method' => 'delete',
                    'controller' => 'EventsController',
                    'cap' => 'wappo_calendar_cancel'
                ],
                '/events/patch' => [
                    'method' => 'patch',
                    'controller' => 'EventsController',
                    'cap' => 'wappo_calendar_reschedule'
                ],
                '/events/put' => [
                    'method' => 'put',
                    'controller' => 'EventsController',
                    'cap' => 'wappo_calendar_confirm'
                ],
                '/events/list' => [
                    'method' => 'get',
                    'controller' => 'EventsController',
                    'cap' => 'wappo_calendar_man'
                ],
                '/services/booking/admin' => [
                    'controller' => 'BookingController',
                    'method' => 'adminBook',
                    'hint' => 'BookingAdmin',
                    'cap' => 'wappo_calendar_book'
                ],
                '/status' => [
                    'method' => 'save',
                    'controller' => 'StatusController',
                    'cap' => 'wappo_calendar_man'
                ],
                '/status/delete' => [
                    'method' => 'delete',
                    'controller' => 'StatusController',
                    'cap' => 'wappo_calendar_man'
                ],
                '/calendars' => [
                    'method' => 'save',
                    'controller' => 'CalendarsController',
                    'cap' => 'wappo_self_weekly'
                ],
                '/calendars/avatar' => [
                    'method' => 'getAvatar',
                    'controller' => 'CalendarsController',
                    'cap' => 'wappo_self_weekly'
                ],
                '/calendars/services' => [
                    'controller' => 'CalendarsController',
                    'method' => 'saveServices',
                    'cap' => 'wappo_self_services',
                ],
                '/calendars/customfields' => [
                    'controller' => 'CalendarsController',
                    'method' => 'saveCustomFields',
                    'cap' => 'wappo_self_weekly',
                ],
                '/wappointment/connect' => [
                    'method' => 'connect',
                    'controller' => 'CalendarsController',
                    'cap' => 'wappo_self_connect_account'
                ],
                '/wappointment/disconnect' => [
                    'method' => 'disconnect',
                    'controller' => 'CalendarsController',
                    'cap' => 'wappo_self_connect_account'
                ],

                '/calendars/savecal' => [
                    'method' => 'saveCal',
                    'controller' => 'CalendarsController',
                    'cap' => 'wappo_self_add_ics'
                ],
                '/calendars/refreshcalendars' => [
                    'controller' => 'CalendarsController',
                    'method' => 'refreshCalendars',
                    'cap' => 'wappo_self_add_ics'
                ],
                '/calendars/disconnect' => [
                    'controller' => 'CalendarsController',
                    'method' => 'disconnectCal',
                    'cap' => 'wappo_self_del_ics'
                ],
                '/calendars/toggle' => [
                    'controller' => 'CalendarsController',
                    'method' => 'toggle',
                    'cap' => 'wappo_self_unpublish'
                ],

                '/client' => [
                    'method' => 'save',
                    'controller' => 'ClientController',
                    'cap' => 'wappo_clients_edit'
                ],
                '/client/delete' => [
                    'method' => 'delete',
                    'controller' => 'ClientController',
                    'cap' => 'wappo_clients_del'
                ],
            ]
        ],
    ];

    public function __construct()
    {
        new Init;
        parent::__construct();
    }
}
