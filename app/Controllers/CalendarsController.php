<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Controllers\RestController;
use Wappointment\Services\VersionDB;
use Wappointment\WP\StaffLegacy;
use Wappointment\Services\Staff as StaffServices;
use Wappointment\Services\Settings;
use Wappointment\Services\DateTime;
use Wappointment\Services\Calendars;
use Wappointment\Managers\Central;
use Wappointment\Repositories\CalendarsBack;
use Wappointment\Repositories\Services;
use Wappointment\Services\CurrentUser;
use Wappointment\Services\ExternalCalendar;
use Wappointment\Services\Permissions;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Wappointment\DotCom;

class CalendarsController extends RestController
{
    public function get()
    {

        $db_update_required = VersionDB::isLessThan(VersionDB::CAN_CREATE_SERVICES);

        $calendars = $db_update_required ? $this->getStafflegacy() : $this->getCalendarsStaff();
        $data = [
            'db_required' => $db_update_required,
            'timezones_list' => DateTime::tz(),
            'calendars' => empty($calendars) ? [] : $calendars,
            'staffs' => StaffServices::getWP(CurrentUser::isAdmin() ? false : CurrentUser::id()),
            'staffDefault' => Settings::staffDefaults(),
            'permissions' => (new Permissions)->getCaps(),
            'allowStaffCf' => Settings::get('allow_staff_cf'),
        ];

        if (!$db_update_required) {
            $data['services'] = (new Services)->get();
            $data['servicesDefault'] = Settings::get('servicesDefault');
            $data['limit_reached'] = Central::get('CalendarModel')::canCreate() ? false : 'To add more calendars, get the "Calendars & Staff" addon';
        }
        return $data;
    }

    public function getCalendarsStaff()
    {
        $calendars = (new CalendarsBack)->get();

        return CurrentUser::isAdmin() ? $calendars : array_values(\WappointmentLv::collect($calendars)->filter(function ($e) {
            return $e['wp_uid'] == CurrentUser::id();
        })->toArray());
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

        $staff_custom_fields = $this->refreshCustomFields($request);

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

        $this->refreshRepository();

        return ['message' => 'CustomFields saved'];
    }

    protected function refreshRepository()
    {
        (new CalendarsBack)->refresh();
    }

    /**
     * Update the custom fields list
     *
     * @param Request $request
     * @return object
     */
    protected function refreshCustomFields(Request $request)
    {
        $staff_custom_fields = \WappointmentLv::collect(WPHelpers::getOption('staff_custom_fields', []));
        if (!empty($request->input('custom_fields'))) {
            $current_cf_keys = $staff_custom_fields->map(function ($e) {
                return $e['key'];
            })->toArray();

            foreach ($request->input('custom_fields') as $custom_field) {
                if (!in_array($custom_field['key'], $current_cf_keys)) {
                    $staff_custom_fields->push($custom_field);
                } else {
                    $keyFound = $staff_custom_fields->search(function ($itm, $key) use ($custom_field) {
                        return $itm['key'] == $custom_field['key'];
                    });
                    $staff_custom_fields->transform(function ($item, $key) use ($keyFound, $custom_field) {
                        return $key == $keyFound ? $custom_field : $item;
                    });
                }
            }

            $deletedFields = $request->input('deleted');
            $new_staff_custom_fields = $staff_custom_fields->reject(function ($value) use ($deletedFields) {
                return in_array($value['key'], $deletedFields);
            });

            WPHelpers::setOption('staff_custom_fields', array_values($new_staff_custom_fields->toArray()));
        }
        return $new_staff_custom_fields;
    }

    public function saveServices(Request $request)
    {
        $this->testIsAllowedToRunQuery('id', $request);
        $calendar = Central::get('CalendarModel')::findOrFail($this->getIdAllowedToSave('id', $request));
        $calendar->services()->sync($request->input('services'));
        $this->refreshRepository();
        return ['message' => 'Services assigned'];
    }

    public function savePermissions(Request $request)
    {
        $this->testIsAllowedToRunQuery('id', $request);
        $calendar = Central::get('CalendarModel')::findOrFail($this->getIdAllowedToSave('id', $request));
        $permissions = new Permissions;
        $permissions->assign($calendar, $request->input('permissions'));
        $this->refreshRepository();
        return ['message' => 'Permissions saved', $request->all()];
    }

    public function saveCal(Request $request)
    {
        $this->testIsAllowedToRunQuery('calendar_id', $request);
        $externalCalendar = new ExternalCalendar($this->getIdAllowedToSave('calendar_id', $request));
        $result = $externalCalendar->save($request->input('calurl'));
        $this->refreshRepository();
        return $result;
    }


    public function getStafflegacy()
    {
        return [(new StaffLegacy)->fullData()];
    }

    public function save(Request $request)
    {
        $this->testIsAllowedToRunQuery('id', $request);

        $data = $request->all();
        $new = false;
        if (empty($data['id'])) {
            $data['sorting'] = Calendars::total();
            $new = true;
        }

        $result = Calendars::save($data);

        if ($new) {
            Calendars::reorder($result->id, 0);
        }


        $this->refreshRepository();
        return ['message' => 'Calendar has been saved', 'result' => $result];
    }

    public function reorder(Request $request)
    {
        $data = $request->only(['id', 'new_sorting']);

        $result = Calendars::reorder($data['id'], $data['new_sorting']);
        $this->refreshRepository();
        return ['message' => 'Calendar has been saved', 'result' => $result];
    }

    public function toggle(Request $request)
    {
        $this->testIsAllowedToRunQuery('id', $request);
        $result = Calendars::toggle($this->getIdAllowedToSave('id', $request));
        $this->refreshRepository();
        return ['message' => 'Calendar has been modified', 'result' => $result];
    }

    public function delete(Request $request)
    {
        $this->testIsAllowedToRunQuery('id', $request);
        Calendars::delete($this->getIdAllowedToSave('id', $request));
        $this->refreshRepository();
        // clean order
        return ['message' => 'Calendar deleted', 'result' => true];
    }

    public function refreshCalendars(Request $request)
    {
        $this->testIsAllowedToRunQuery('staff_id', $request);
        $externalCalendar = new ExternalCalendar($this->getIdAllowedToSave('staff_id', $request));
        $result = $externalCalendar->refreshCalendars(true);
        $this->refreshRepository();
        return $result;
    }

    public function disconnectCal(Request $request)
    {
        if (is_array($request->input('calendar_id'))) {
            throw new \WappointmentException("Malformed parameter", 1);
        }
        $this->testIsAllowedToRunQuery('staff_id', $request);

        $externalCalendar = new ExternalCalendar($this->getIdAllowedToSave('staff_id', $request));
        $result = $externalCalendar->disconnect($request->input('calendar_id'));
        $this->refreshRepository();
        return $result;
    }

    public function connect(Request $request)
    {
        $staff_id = !empty($request->input('id')) ? $request->input('id') : Settings::get('activeStaffId');
        $dotcomapi = new DotCom;
        $dotcomapi->setStaff($staff_id);
        $result = $dotcomapi->connect($request->get('account_key'));

        if ($result) {
            $this->refreshRepository();
            return [
                'data' => $result['dotcom'],
                'message' => 'Account has been connected'
            ];
        }
        throw new \WappointmentException("Couldn't connect with this key.", 1);
    }

    public function disconnect(Request $request)
    {
        $staff_id = !empty($request->input('id')) ? $request->input('id') : Settings::get('activeStaffId');
        $dotcom = new DotCom;
        $dotcom->setStaff($staff_id);
        $result = $dotcom->disconnect($staff_id);

        if ($result) {
            $this->refreshRepository();
            return [
                'data' => $result,
                'message' => 'Account has been disconnected'
            ];
        }
        throw new \WappointmentException("Couldn't disconnect account.", 1);
    }

    public function refresh(Request $request)
    {
        $staff_id = !empty($request->input('id')) ? $request->input('id') : Settings::get('activeStaffId');
        $dotcom = new DotCom;
        $dotcom->setStaff($staff_id);
        $result = $dotcom->refresh();

        if ($result) {
            $this->refreshRepository();
            return [
                'data' => $result,
                'message' => 'Account has been refreshed'
            ];
        }
        throw new \WappointmentException("Couldn't refresh account.", 1);
    }
}
