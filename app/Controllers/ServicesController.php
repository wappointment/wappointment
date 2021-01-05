<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Controllers\RestController;
use Wappointment\Models\Service as ServiceModel;
use Wappointment\Services\Services;
use Wappointment\System\Status;

class ServicesController extends RestController
{
    public function get()
    {

        if (version_compare(Status::dbVersion(), '2.1.0') < 0) {
            throw new \WappointmentException("You must run a database update first", 1);
        }

        return ServiceModel::orderBy('sorting')->take(3)->get();
    }

    public function save(Request $request)
    {
        $data = $request->only(['id', 'name', 'options', 'locations_id']);
        if (empty($data['id'])) {
            $data['sorting'] = Services::total();
        }
        $result = Services::save($data);
        return ['message' => 'Service has been saved', 'result' => $result];
    }

    public function reorder(Request $request)
    {
        $data = $request->only(['id', 'new_sorting']);

        $result = Services::reorder($data['id'], $data['new_sorting']);
        return ['message' => 'Service has been saved', 'result' => $result];
    }


    public function delete(Request $request)
    {
        Services::delete($request->input('id'));
        // clean order
        return ['message' => 'Service deleted', 'result' => true];
    }
}
