<?php

namespace Wappointment\Services;

class Jitsi
{
    public static $base = 'https://meet.jit.si/';

    public static function generate($appointment)
    {
        return static::baseUrl() . $appointment->getIdentifier();
    }

    public static function baseUrl()
    {
        $jitsi_url = Settings::get('jitsi_url');
        return empty($jitsi_url) ? static::$base : $jitsi_url;
    }
}
