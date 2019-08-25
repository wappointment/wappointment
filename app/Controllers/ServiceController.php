<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Service;
use Wappointment\ClassConnect\Request;

class ServiceController extends RestController
{
    public function save(Request $request)
    {
        return $this->isTrueOrFail(Service::save($request->only(['name', 'duration', 'type', 'address', 'options'])));
    }
}
