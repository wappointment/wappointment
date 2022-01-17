<?php

namespace Wappointment\Services;

class Jitsi
{
    public static $base = 'https://meet.jit.si/';

    public static function generate($appointment)
    {
        return static::$base . $appointment->getIdentifier();
    }
}
