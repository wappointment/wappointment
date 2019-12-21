<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Settings;
use Wappointment\ClassConnect\Request;
use Wappointment\WP\Helpers as WPHelpers;

class SettingsStaffController extends RestController
{
    public function save(Request $request)
    {
        if ($request->input('key') == 'viewed_updates') {
            return WPHelpers::setStaffOption(
                'viewed_updates',
                WAPPOINTMENT_VERSION,
                Settings::get('activeStaffId'),
                true
            );
        }
        if ($request->input('key') == 'hello_page') {
            return WPHelpers::setStaffOption(
                'hello_page',
                $request->input('val'),
                Settings::get('activeStaffId'),
                true
            );
        }

        $result = Settings::saveStaff($request->input('key'), $request->input('val'));
        if ($request->input('key') == 'calurl') {
            $result['last_checked'] = WPHelpers::getStaffOption('last-calendar-checked');
            $result['last_parsed'] = WPHelpers::getStaffOption('last-calendar-parsed');
            $result['last_process'] = WPHelpers::getStaffOption('last-calendar-process');
            $result['calurl'] = Settings::getStaff('calurl');
            $result['timezone'] = Settings::getStaff('timezone');
        }
        if ($request->input('key') == 'regav') {
            (new \Wappointment\Services\Availability())->regenerate();
        }


        return $result;
    }

    public function get(Request $request)
    {
        return Settings::getStaff($request->input('key'));
    }
}
