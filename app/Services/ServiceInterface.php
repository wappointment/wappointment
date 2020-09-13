<?php

namespace Wappointment\Services;

interface ServiceInterface
{
    public static function save($serviceData);

    public static function saveService($serviceData);

    public static function get($service_id = false);

    public static function getObject($service_id = false);

    public static function all();
    public static function patch($service_id, $data);
}
