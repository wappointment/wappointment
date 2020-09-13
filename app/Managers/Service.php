<?php

namespace Wappointment\Managers;

class Service
{
    public static function all()
    {
        return Central::get('Service')::all();
    }

    public static function save($data)
    {
        return Central::get('Service')::saveService($data);
    }

    public static function patch($service_id, $data)
    {
        return Central::get('Service')::patch($service_id, $data);
    }
}
