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
}
