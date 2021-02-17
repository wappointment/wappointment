<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Controllers\RestController;
use Wappointment\Models\Service as CalendarsModel;
use Wappointment\Services\Services;
use Wappointment\Services\VersionDB;
use Wappointment\WP\Staff;
use Wappointment\Services\Staff as StaffServices;
use Wappointment\Services\DateTime;

class CalendarsController extends RestController
{
    public function get()
    {
        $db_update_required = VersionDB::isLessThan(VersionDB::CAN_CREATE_SERVICES);
        $calendars = $db_update_required ? $this->getlegacy() : CalendarsModel::orderBy('sorting')->take(2)->get();
        return [
            'db_required' => $db_update_required,
            'timezones_list' => DateTime::tz(),
            'calendars' => $calendars,
            'staffs' => StaffServices::getWP(),
        ];
    }

    public function getlegacy()
    {
        return [
            (new Staff)->fullData()
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
