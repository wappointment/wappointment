<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Controllers\RestController;
use Wappointment\Services\VersionDB;
use Wappointment\WP\StaffLegacy;
use Wappointment\WP\Staff;
use Wappointment\Services\Staff as StaffServices;
use Wappointment\Services\Settings;
use Wappointment\Services\DateTime;
use Wappointment\Services\Calendars;
use Wappointment\Managers\Central;
use Wappointment\Services\ExternalCalendar;
use Wappointment\Services\Permissions;

class CalendarsController extends RestController
{
    public function get()
    {

        $db_update_required = VersionDB::isLessThan(VersionDB::CAN_CREATE_SERVICES);

        $calendars = $db_update_required ? $this->getStafflegacy() : $this->getCalendarsStaff();
        $data = [
            'db_required' => $db_update_required,
            'timezones_list' => DateTime::tz(),
            'calendars' => $calendars,
            'staffs' => StaffServices::getWP(),
            'staffDefault' => Settings::staffDefaults(),
            'permissions' => (new Permissions)->getCaps(),
        ];
        if (!$db_update_required) {
            $data['services'] = Central::get('ServiceModel')::orderBy('sorting')->fetch();
            $data['limit_reached'] = Central::get('CalendarModel')::canCreate() ? false : 'To add more calendars, get the "Calendars & Staff" addon';
        }
        return $data;
    }

    public function getCalendarsStaff()
    {
        $calendars = Central::get('CalendarModel')::orderBy('sorting')->with(['services'])->fetch();
        $staffs = [];
        foreach ($calendars->toArray() as $calendar) {
            $staffs[] = (new Staff($calendar))->fullData();
        }
        return $staffs;
    }


    public function getAvatar(Request $request)
    {
        $avatar = wp_get_attachment_image_src((int)$request->input('id'));
        return ['avatar' => $avatar[0], 'id' => (int)$request->input('id')];
    }


    public function saveServices(Request $request)
    {
        $calendar = Central::get('CalendarModel')::findOrFail((int)$request->input('id'));
        $calendar->services()->sync($request->input('services'));
        return ['message' => 'Calendar saved'];
    }

    public function savePermissions(Request $request)
    {
        $calendar = Central::get('CalendarModel')::findOrFail((int)$request->input('id'));
        $permissions = new Permissions;
        $permissions->assign($calendar, $request->input('permissions'));
        return ['message' => 'Permissions saved', $request->all()];
    }

    public function saveCal(Request $request)
    {
        $calendar_id = empty($request->input('calendar_id')) ? false : (int)$request->input('calendar_id');
        $externalCalendar = new ExternalCalendar($calendar_id);
        return $externalCalendar->save($request->input('calurl'));
    }


    public function getStafflegacy()
    {
        return [
            (new StaffLegacy)->fullData()
        ];
    }

    public function save(Request $request)
    {

        $data = $request->all();
        if (empty($data['id'])) {
            $data['sorting'] = Calendars::total();
        }
        $result = Calendars::save($data);
        return ['message' => 'Calendar has been saved', 'result' => $result];
    }

    public function reorder(Request $request)
    {
        $data = $request->only(['id', 'new_sorting']);

        $result = Calendars::reorder($data['id'], $data['new_sorting']);
        return ['message' => 'Calendar has been saved', 'result' => $result];
    }

    public function toggle(Request $request)
    {
        $data = $request->only(['id']);

        $result = Calendars::toggle((int)$data['id']);
        return ['message' => 'Calendar has been modified', 'result' => $result];
    }

    public function delete(Request $request)
    {
        Calendars::delete((int)$request->input('id'));
        // clean order
        return ['message' => 'Calendar deleted', 'result' => true];
    }

    public function refreshCalendars(Request $request)
    {
        $externalCalendar = new ExternalCalendar((int)$request->input('staff_id'));
        return $externalCalendar->refreshCalendars(true);
    }

    public function disconnectCal(Request $request)
    {
        if (is_array($request->input('calendar_id'))) {
            throw new \WappointmentException("Malformed parameter", 1);
        }

        $externalCalendar = new ExternalCalendar((int)$request->input('staff_id'));
        return $externalCalendar->disconnect($request->input('calendar_id'));
    }
}
