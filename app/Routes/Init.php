<?php

namespace Wappointment\Routes;

class Init extends AbstractRoutes
{
    protected $routes = [
        'administrator' => [
            'GET' => [
                '/viewsdata/(?P<key>\S+)' => [
                    'controller' => 'ViewsDataController',
                    'method' => 'get',
                ]
            ],
            'POST' => [
                '/wizardlater' => [
                    'controller' => 'WizardController',
                    'method' => 'later',
                ],
                '/wizard' => [
                    'controller' => 'WizardController',
                    'method' => 'setStep',
                ],
                '/wappointment/contact' => [
                    'controller' => 'WappointmentController',
                    'method' => 'contact',
                    'hint' => 'ContactAdmin'
                ],
            ],
        ]
    ];
}
