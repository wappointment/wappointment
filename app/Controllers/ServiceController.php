<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Services;
use Wappointment\ClassConnect\Request;

class ServiceController extends RestController
{
    public function save(Request $request)
    {
        $data = $request->only(['id', 'name', 'options', 'locations_id']);
        if (empty($data['id'])) {
            $data['sorting'] = Services::total();
        }
        Services::save($data);

        return ['message' => 'Service saved'];
    }
}
