<?php

namespace Wappointment\Services;

class Staff
{
    public static function getIds()
    {
        $multipleStaff = false;
        if ($multipleStaff) { } else {
            return [Settings::get('activeStaffId')];
        }
    }
}
