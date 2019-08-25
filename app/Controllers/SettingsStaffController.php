<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Settings;
use Wappointment\ClassConnect\Request;
use Wappointment\WP\Helpers as WPHelpers;

class SettingsStaffController extends RestController
{
    public function save(Request $request)
    {
        $result = Settings::saveStaff($request->input('key'), $request->input('val'));
        if ($request->input('key') == 'calurl') {
            $result['last_checked'] = WPHelpers::getStaffOption('last-calendar-checked');
            $result['last_parsed'] = WPHelpers::getStaffOption('last-calendar-parsed');
            $result['last_process'] = WPHelpers::getStaffOption('last-calendar-process');
            $result['calurl'] = Settings::getStaff('calurl');
            $result['timezone'] = Settings::getStaff('timezone');
        }
        return $result;
    }

    public function get(Request $request)
    {
        return Settings::getStaff($request->input('key'));
    }
}
