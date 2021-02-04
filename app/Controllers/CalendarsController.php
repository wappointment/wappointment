<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Controllers\RestController;
use Wappointment\Models\Service as ServiceModel;
use Wappointment\Services\Services;
use Wappointment\Services\VersionDB;
use Wappointment\Services\Settings;
use Wappointment\Services\Staff;

class CalendarsController extends RestController
{
    public function get()
    {
        if (VersionDB::isLessThan(VersionDB::CAN_CREATE_SERVICES)) {
            return $this->getlegacy();
        }

        return ServiceModel::orderBy('sorting')->take(2)->get();
    }

    public function getlegacy()
    {
        return [
            [
                'name' => Staff::getName(),
                'regav' => Settings::getStaff('regav'),
                'tz' => Settings::getStaff('timezone'),
                'services' => [
                    \Wappointment\Services\Service::get()
                ],
                'connected' => Settings::getStaff('dotcom'),
            ]
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
