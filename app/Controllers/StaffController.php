<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Controllers\RestController;
use Wappointment\Services\VersionDB;
use Wappointment\Managers\Central;

class StaffController extends RestController
{

    public function get()
    {
        $serviceModel = Central::get('CalendarModel')::model();
        $db_update_required = VersionDB::isLessThan(VersionDB::CAN_CREATE_SERVICES);
        $services = $db_update_required ? $this->getlegacy() : $serviceModel::orderBy('sorting')->fetch();

        return [
            'limit_reached' => $serviceModel::canCreate() ? false : $serviceModel::MaxRows() . ' services max allowed',
            'db_required' => $db_update_required,
            'services' => $services,
        ];
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
