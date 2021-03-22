<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Controllers\RestController;
use Wappointment\Models\Location;
use Wappointment\Services\Location as LocationService;

class LocationsController extends RestController
{
    public function get()
    {
        return Location::get();
    }

    public function save(Request $request)
    {
        $result = LocationService::save($request->only(['id', 'name', 'type', 'options']));
        return ['message' => 'Modality has been saved', 'result' => $result, 'locations' => $this->get()];
    }

    public function delete(Request $request)
    {
        return ['message' => 'Modality deleted', 'result' => Location::destroy($request->input('id')), 'deleted' => $request->input('id')];
    }
}
