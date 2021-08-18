<?php

namespace Wappointment\WP;

class Alerts
{

    private static $messages = [];

    public static function error($message)
    {
        self::$messages[] = $message;
    }

    public static function get()
    {
        return self::$messages;
    }
}
