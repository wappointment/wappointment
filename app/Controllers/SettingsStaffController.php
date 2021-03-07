<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Settings;
use Wappointment\ClassConnect\Request;
use Wappointment\System\Status;
use Wappointment\Services\Calendars;

class SettingsStaffController extends RestController
{
    public function get(Request $request)
    {
        return Settings::getStaff($request->input('key'));
    }

    public function save(Request $request)
    {
        if ($request->input('key') == 'viewed_updates') {
            return Status::setViewedUpdated();
        }
        $value = $request->input('val');
        if ($request->input('key') == 'regav') { //legacy
            $value = Calendars::regavClean($value); //clean invalid entry in regav
        }
        $result = Settings::saveStaff($request->input('key'), $value);

        //TODO Legacy remove at some point
        if (in_array($request->input('key'), ['regav', 'availaible_booking_days'])) {
            (new \Wappointment\Services\Availability())->regenerate(); //legacy
        }

        return $result;
    }
}
