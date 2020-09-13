<?php

namespace Wappointment\Services;

class Addons
{
    public static function getActive()
    {
        return apply_filters('wappointment_active_addons', []);
    }

    public static function isActive($addon_name)
    {
        static $addons = false;
        if ($addons === false) {
            $addons = static::getActive();
        }
        return !empty($addons[$addon_name]);
    }
}
