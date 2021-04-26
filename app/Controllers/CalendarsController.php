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
use Wappointment\Services\CurrentUser;
use Wappointment\Services\ExternalCalendar;
use Wappointment\Services\Permissions;
use Wappointment\WP\Helpers as WPHelpers;

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
            'staffs' => StaffServices::getWP(CurrentUser::isAdmin() ? false : CurrentUser::id()),
            'staffDefault' => Settings::staffDefaults(),
            'permissions' => (new Permissions)->getCaps(),
            'allowStaffCf' => Settings::get('allow_staff_cf'),
        ];
        if (!$db_update_required) {
            $data['services'] = Central::get('ServiceModel')::orderBy('sorting')->fetch();
            $data['limit_reached'] = Central::get('CalendarModel')::canCreate() ? false : 'To add more calendars, get the "Calendars & Staff" addon';
        }
        return $data;
    }

    public function getCalendarsStaff()
    {
        $calendarsQry = Central::get('CalendarModel')::orderBy('sorting')->with(['services']);
        if (!CurrentUser::isAdmin()) {
            $calendarsQry->where('id', CurrentUser::calendarId());
        }

        $calendars = $calendarsQry->fetch();
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

    protected function getIdAllowedToSave($idName, Request $request)
    {
        return CurrentUser::isAdmin() ? (int)$request->input($idName) : CurrentUser::calendarId();
    }

    public function testIsAllowedToRunQuery($idName, Request $request)
    {
        if (!CurrentUser::isAdmin() && (int)CurrentUser::calendarId() !== (int)$request->input($idName)) {
            throw new \WappointmentException("You are not allowed to run that request", 1);
        }
    }

    public function saveCustomFields(Request $request)
    {
        $this->testIsAllowedToRunQuery('id', $request);
        $calendar = Central::get('CalendarModel')::findOrFail($this->getIdAllowedToSave('id', $request));

        $staff_custom_fields = \WappointmentLv::collect(WPHelpers::getOption('staff_custom_fields', []));
        if (!empty($request->input('custom_fields'))) {
            $array_key = $staff_custom_fields->map(function ($e) {
                return $e['key'];
            })->toArray();

            foreach ($request->input('custom_fields') as $custom_field) {
                if (!in_array($custom_field['key'], $array_key)) {
                    $staff_custom_fields->push($custom_field);
                }
            }

            WPHelpers::setOption('staff_custom_fields', $staff_custom_fields->toArray());
        }
        $options = $calendar->options;
        if (empty($options['custom_fields'])) {
            $options['custom_fields'] = [];
        }
        if (!empty($request->input('values'))) {
            $cf_can_save = $staff_custom_fields->map(function ($e) {
                return $e['key'];
            })->toArray();
            foreach ($request->input('values') as $key => $value) {
                if (in_array($key, $cf_can_save)) {
                    $options['custom_fields'][$key] = $value;
                }
            }
        }
        $calendar->options = $options;
        $calendar->save();

        return ['message' => 'CustomFields saved'];
    }

    public function saveServices(Request $request)
    {
        $this->testIsAllowedToRunQuery('id', $request);
        $calendar = Central::get('CalendarModel')::findOrFail($this->getIdAllowedToSave('id', $request));
        $calendar->services()->sync($request->input('services'));
        return ['message' => 'Services assigned'];
    }

    public function savePermissions(Request $request)
    {
        $this->testIsAllowedToRunQuery('id', $request);
        $calendar = Central::get('CalendarModel')::findOrFail($this->getIdAllowedToSave('id', $request));
        $permissions = new Permissions;
        $permissions->assign($calendar, $request->input('permissions'));
        return ['message' => 'Permissions saved', $request->all()];
    }

    public function saveCal(Request $request)
    {
        $this->testIsAllowedToRunQuery('calendar_id', $request);
        $externalCalendar = new ExternalCalendar($this->getIdAllowedToSave('calendar_id', $request));
        return $externalCalendar->save($request->input('calurl'));
    }


    public function getStafflegacy()
    {
        return [(new StaffLegacy)->fullData()];
    }

    public function save(Request $request)
    {
        $this->testIsAllowedToRunQuery('id', $request);

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
        $this->testIsAllowedToRunQuery('id', $request);
        $result = Calendars::toggle($this->getIdAllowedToSave('id', $request));
        return ['message' => 'Calendar has been modified', 'result' => $result];
    }

    public function delete(Request $request)
    {
        $this->testIsAllowedToRunQuery('id', $request);
        Calendars::delete($this->getIdAllowedToSave('id', $request));
        // clean order
        return ['message' => 'Calendar deleted', 'result' => true];
    }

    public function refreshCalendars(Request $request)
    {
        $this->testIsAllowedToRunQuery('staff_id', $request);
        $externalCalendar = new ExternalCalendar($this->getIdAllowedToSave('staff_id', $request));
        return $externalCalendar->refreshCalendars(true);
    }

    public function disconnectCal(Request $request)
    {
        if (is_array($request->input('calendar_id'))) {
            throw new \WappointmentException("Malformed parameter", 1);
        }
        $this->testIsAllowedToRunQuery('staff_id', $request);

        $externalCalendar = new ExternalCalendar($this->getIdAllowedToSave('staff_id', $request));
        return $externalCalendar->disconnect($request->input('calendar_id'));
    }
}
