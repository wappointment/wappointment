<?php

namespace Wappointment\Helpers;

use Wappointment\Services\Service as ServiceService;
use Wappointment\Services\Services as ServiceServices;

class Service
{
    public static function hasMultipleTypes($legacy = false)
    {
        return $legacy ? static::hasMultipleTypeslegacy() : static::hasMultipleTypesNew();
    }

    protected static function hasMultipleTypeslegacy()
    {
        $service = ServiceService::get();
        return !empty($service['type']) && count($service['type']) > 1;
    }

    protected static function hasMultipleTypesNew()
    {
        $services = ServiceServices::all();
        $types = [];
        foreach ($services as $service) {
            foreach (static::getLocations($service) as $location) {
                $types[static::getLocationId($location)] = $location;
            }
        }
        return count($types) > 1;
    }

    public static function getLocations($service)
    {
        return is_array($service) ? $service['locations'] : $service->locations;
    }

    public static function getLocationId($location)
    {
        return is_array($location) ? $location['id'] : $location->id;
    }
}
