<?php

namespace Wappointment\Services;

use Wappointment\Helpers\Events;

class JobHelper
{
    public static function dispatch($eventName, $data, $client = null, $status = 0)
    {
        static::triggerCommons($eventName, $data);

        Events::dispatch(
            $eventName,
            Filters::prepareEmailData(
                $data,
                $client
            )
        );
    }

    public static function dcCreate($appointment)
    {
        static::dotcom('create', $appointment);
    }

    public static function dcCancel($appointment)
    {
        static::dotcom('cancel', $appointment);
    }

    public static function dcUpdate($appointment)
    {
        static::dotcom('update', $appointment);
    }

    private static function triggerCommons($eventName, $data)
    {
        switch ($eventName) {
            case 'AppointmentConfirmedEvent':
            case 'AppointmentRescheduledEvent':
                JobHelper::dcUpdate($data['appointment']);
                break;

            default:
                # code...
                break;
        }
    }
    private static function dotcom($event, $appointment)
    {
        \Wappointment\Services\Queue::push(
            'Wappointment\Jobs\ProcessTransaction',
            ['event' => $event, 'appointment' => $appointment],
            'api'
        );
    }
}
