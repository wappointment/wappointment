<?php

namespace Wappointment\Services;

class Addons
{
    public static function getActive()
    {
        return apply_filters('wappointment_active_addons', []);
    }
}
