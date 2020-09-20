<?php

namespace Wappointment\Managers;

class Client
{
    public static function model()
    {
        return Central::get('Client');
    }
}
