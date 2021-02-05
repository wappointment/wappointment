<?php

namespace Wappointment\Services;

use Wappointment\Models\WPUser;
use Wappointment\Models\WPUserMeta;
use Wappointment\ClassConnect\Request;

class Staff
{
    public static function getById($staff_id)
    {
        $staff_id = empty($staff_id) ? Settings::get('activeStaffId') : $staff_id;
        return (new \Wappointment\WP\Staff($staff_id));
    }

    public static function get()
    {
        $staffs = [];
        foreach (static::getIds() as $staff_id) {
            $staffs[$staff_id] = (new \Wappointment\WP\Staff($staff_id))->toArray();
        }
        return $staffs;
    }

    public static function getName()
    {
        return (new \Wappointment\WP\Staff(Settings::get('activeStaffId')))->name;
    }

    public static function getIds()
    {
        return [Settings::get('activeStaffId')];
    }

    public static function getWP()
    {
        return WPUser::whereIn('ID', WPUserMeta::getUserIdWithRoles())->get();
    }

    public static function getStaffId(Request $request)
    {
        $staff_id = Settings::get('activeStaffId');
        if (empty($request->input('staff_id'))) {
            return $staff_id;
        } else {
            //if user has administrative role it returns which ever
            return current_user_can('administrator') ? (int) $request->input('staff_id') : $staff_id;
        }
    }
}
