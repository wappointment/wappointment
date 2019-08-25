<?php

namespace Wappointment\Controllers;


use Wappointment\Services\ViewsData;
use Wappointment\ClassConnect\Request;

class AvailabilityController extends RestController
{
    public function get(Request $request)
    {
        return (new ViewsData())->load('front_availability');
    }
}
