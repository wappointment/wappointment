<?php

namespace Wappointment\Helpers;

use Wappointment\Services\Settings;

class Service
{
    public static function hasMultipleTypes()
    {
        $service = Settings::get('service');
        return !empty($service['type']) && count($service['type']) > 1;
    }
}
