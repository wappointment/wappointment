<?php

namespace Wappointment\Services;

use Wappointment\Helpers\Events;

class JobHelper
{
    public static function dispatch($eventName, $data, $client = null, $status = 0)
    {
        Events::dispatch(
            $eventName,
            Filters::prepareEmailData(
                $data,
                $client
            )
        );
    }
}
