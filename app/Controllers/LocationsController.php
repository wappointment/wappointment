<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Controllers\RestController;
use Wappointment\Models\Location;

class LocationsController extends RestController
{
    public function get()
    {
        return Location::get();
    }

    public function save(Request $request)
    {
        $id = $request->input('id');
        if (!empty($id)) {
            $location = Location::find($id);
            $result = $location->update($request->only(['name', 'type', 'options']));
        } else {
            $result = Location::create($request->only(['name', 'type', 'options']));
        }
        return ['message' => 'Location has been saved', 'result' => $result, 'locations' => $this->get()];
    }

    public function delete(Request $request)
    {
        return ['message' => 'Location deleted', 'result' => Location::destroy($request->input('id')), 'deleted' => $request->input('id')];
    }
}
