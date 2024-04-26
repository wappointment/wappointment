<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Controllers\RestController;
use Wappointment\Helpers\Get;
use Wappointment\Helpers\Translations;
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

        $services = (new Services())->get();
        $data = [
            'db_required' => $db_update_required,
            'timezones_list' => DateTime::tz(),
            'calendars' => empty($calendars) ? [] : $this->filterCalendarServices($calendars, $services),
            'staffs' => StaffServices::getWP(CurrentUser::isAdmin() ? false : CurrentUser::id()),
            'staffDefault' => Settings::staffDefaults(),
            'permissions' => (new Permissions())->getCaps(),
            'allowStaffCf' => Settings::get('allow_staff_cf'),
        ];

        if (!$db_update_required) {
            $data['services'] = $services;
            $data['servicesDefault'] = Settings::get('servicesDefault');
            $data['limit_reached'] = Central::get('CalendarModel')::canCreate() ? false : Translations::get('add_calendars_addon', [Get::list('addons')['wappointment_staff']['name']]);
        }
        return $data;
    }

    private function getServiceIDs($services)
    {
        $service_ids = [];
        foreach ($services as $service) {
            $service_ids[] = $service['id'];
        }
        return $service_ids;
    }

    /**
     * get rid of deleted services
     *
     * @return void
     */
    private function filterCalendarServices($calendars, $services)
    {
        $service_ids = $this->getServiceIDs($services);
        foreach ($calendars as $key => $calendar) {
            foreach ($calendar['services'] as $keyid => $service_id) {
                if (!in_array($service_id, $service_ids)) {
                    unset($calendars[$key]['services'][$keyid]);
                }
            }
            $calendars[$key]['services'] = array_values($calendars[$key]['services']);
        }
        return $calendars;
    }

    public function getCalendarsStaff()
    {
        $calendars = (new CalendarsBack())->get();

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
        if (!CurrentUser::isAdmin() && !CurrentUser::owns((int)$request->input($idName))) {
            throw new \WappointmentException(Translations::get('forbidden'), 1);
        }
    }

    public function getCFStructure(Request $request)
    {
        return [
            'custom_fields' => WPHelpers::getOption('staff_custom_fields', [])
        ];
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

        return ['message' => Translations::get('element_saved')];
    }

    protected function refreshRepository()
    {
        (new CalendarsBack())->refresh();
    }

    /**
     * Update the custom fields list
     *
     * @param Request $request
     * @return object
     */
    protected function refreshCustomFields(Request $request)
    {
        $new_staff_custom_fields = $staff_custom_fields = \WappointmentLv::collect(WPHelpers::getOption('staff_custom_fields', []));
        if (WPHelpers::canManageWappo() && !empty($request->input('custom_fields'))) {
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
        return ['message' => __('Services assigned', 'wappointment')];
    }

    public function savePermissions(Request $request)
    {
        $this->testIsAllowedToRunQuery('id', $request);
        $calendar = Central::get('CalendarModel')::findOrFail($this->getIdAllowedToSave('id', $request));
        $permissions = new Permissions();
        $permissions->assign($calendar, $request->input('permissions'));
        $this->refreshRepository();
        return ['message' => __('Permissions saved', 'wappointment'), $request->all()];
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
        return [(new StaffLegacy())->fullData()];
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
        return ['message' => Translations::get('element_saved'), 'result' => $result];
    }

    public function reorder(Request $request)
    {
        $data = $request->only(['id', 'new_sorting']);

        $result = Calendars::reorder($data['id'], $data['new_sorting']);
        $this->refreshRepository();
        return ['message' => Translations::get('element_reordered'), 'result' => $result];
    }

    public function toggle(Request $request)
    {
        $this->testIsAllowedToRunQuery('id', $request);
        $result = Calendars::toggle($this->getIdAllowedToSave('id', $request));
        $this->refreshRepository();
        return ['message' => Translations::get('element_updated'), 'result' => $result];
    }

    public function delete(Request $request)
    {
        $this->testIsAllowedToRunQuery('id', $request);
        Calendars::delete($this->getIdAllowedToSave('id', $request));
        $this->refreshRepository();
        // clean order
        return ['message' => Translations::get('element_deleted'), 'result' => true];
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
            throw new \WappointmentException(__('Malformed parameter', 'wappointment'), 1);
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
        $dotcomapi = new DotCom();
        $dotcomapi->setStaff($staff_id);
        $result = $dotcomapi->connect($request->get('account_key'));

        if ($result) {
            $this->refreshRepository();
            return [
                'data' => $result['dotcom'],
                'message' => __('Connected', 'wappointment')
            ];
        }
        throw new \WappointmentException(__('Error connecting', 'wappointment'), 1);
    }

    public function disconnect(Request $request)
    {
        $staff_id = !empty($request->input('id')) ? $request->input('id') : Settings::get('activeStaffId');
        $dotcom = new DotCom();
        $dotcom->setStaff($staff_id);
        $result = $dotcom->disconnect($staff_id);

        if ($result) {
            $this->refreshRepository();
            return [
                'data' => $result,
                'message' => __('Disconnected', 'wappointment')
            ];
        }
        throw new \WappointmentException(__('Error disconnecting', 'wappointment'), 1);
    }

    public function refresh(Request $request)
    {
        $staff_id = !empty($request->input('id')) ? $request->input('id') : Settings::get('activeStaffId');
        $dotcom = new DotCom();
        $dotcom->setStaff($staff_id);
        $result = $dotcom->refresh();

        if ($result) {
            $this->refreshRepository();
            return [
                'data' => $result,
                'message' => __('Refreshed', 'wappointment')
            ];
        }
        throw new \WappointmentException(__('Error refreshing', 'wappointment'), 1);
    }
}
