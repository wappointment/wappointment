<?php

namespace Wappointment\Controllers;

use Wappointment\Services\ViewsData;
use Wappointment\ClassConnect\Request;

class AvailabilityController extends RestController
{
    public function get(Request $request)
    {
        //return json_decode(file_get_contents(dirname(dirname(dirname(__FILE__))) . '/test_availability.json'));
        return (new ViewsData())->load('front_availability');
    }
}
