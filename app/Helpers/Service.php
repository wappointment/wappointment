<?php

namespace Wappointment\Helpers;

use Wappointment\Services\Service as ServiceService;

class Service
{
    public static function hasMultipleTypes()
    {
        $service = ServiceService::get();
        return !empty($service['type']) && count($service['type']) > 1;
    }
}
