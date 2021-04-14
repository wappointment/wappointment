<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Controllers\RestController;
use Wappointment\Models\Location;
use Wappointment\Services\Location as LocationService;

class LocationsController extends RestController
{
    public function get(Request $request)
    {
        $locations = Location::get();
        if ($request->input('usable')) {
            $locations = $locations->filter(function ($value) {
                if ($value->type == Location::TYPE_AT_LOCATION && empty($value->options['address'])) {
                    return false;
                }
                if ($value->type == Location::TYPE_PHONE && empty($value->options['countries'])) {
                    return false;
                }
                if ($value->type == Location::TYPE_ZOOM && empty($value->options['video'])) {
                    return false;
                }

                return true;
            });
        }
        return array_values($locations->toArray());
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
