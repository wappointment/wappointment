<?php

namespace Wappointment\Services;

use Wappointment\Models\WPUser;
use Wappointment\ClassConnect\Request;
use Wappointment\WP\Staff as WPStaff;
use Wappointment\WP\StaffLegacy;

class Staff
{
    public static function getById($staff_id)
    {
        $staff_id = empty($staff_id) ? Settings::get('activeStaffId') : $staff_id;
        return (new StaffLegacy($staff_id));
    }

    public static function get()
    {
        $db_update_required = VersionDB::isLessThan(VersionDB::CAN_CREATE_SERVICES);
        return $db_update_required ? static::getStafflegacy() : static::getCalendarsStaff();
    }

    public static function getCalendarsStaff()
    {
        $calendars = Calendars::all(true);
        $staffs = [];
        foreach ($calendars->toArray() as $key => $calendar) {
            $staffs[] = (new WPStaff($calendar))->toArray();
        }
        return $staffs;
    }

    public static function getStafflegacy()
    {
        return [
            (new StaffLegacy)->toArray()
        ];
    }


    public static function getNameLegacy()
    {
        return (new StaffLegacy(Settings::get('activeStaffId')))->name;
    }

    public static function getIds()
    {
        return [Settings::get('activeStaffId')];
    }

    public static function getWP($id = false)
    {
        $wp_users = $id === false ? static::getUserByRoles() : [static::getUserById($id)];

        foreach ($wp_users as $key => $wp_user) {
            $wp_users[$key] = WPUser::parseUserObject($wp_user);
        }

        return $wp_users;
    }

    public static function getUserByRoles()
    {
        return get_users(['role__in' => Settings::get('calendar_roles')]);
    }

    public static function getUserById($id)
    {
        return get_user_by('id', (int)$id);
        return WPUser::whereIn('ID', [(int)$id])
            ->select(['ID', 'user_login', 'user_nicename', 'display_name', 'user_email'])
            ->get();
    }

    public static function getStaffId(Request $request)
    {
        $staff_id = Settings::get('activeStaffId');
        if (empty($request->input('staff_id'))) {
            return $staff_id;
        } else {
            //if user has administrative role it returns which ever
            return  static::isAdminOrManager() ? (int) $request->input('staff_id') : $staff_id;
        }
    }

    public static function isAdminOrManager()
    {
        return current_user_can('administrator') || current_user_can('wappointment_manager');
    }
}
