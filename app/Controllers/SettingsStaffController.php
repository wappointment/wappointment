<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Settings;
use Wappointment\ClassConnect\Request;
use Wappointment\WP\Helpers as WPHelpers;

class SettingsStaffController extends RestController
{
    public function get(Request $request)
    {
        return Settings::getStaff($request->input('key'));
    }

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

        if ($request->input('key') == 'regav') {
            (new \Wappointment\Services\Availability())->regenerate();
        }

        return $result;
    }

    public function saveCal(Request $request)
    {
        $calurl = $request->input('calurl');
        $result = $this->calurlValid($calurl);
        if (!$result) {
            throw new \WappointmentException('Cannot save calendar verify it respects the Ical ics format');
        }

        $calurls = WPHelpers::getStaffOption('cal_urls');
        if (empty($calurls)) {
            $calurls = [];
        }
        if (isset($calurls[md5($calurl)])) {
            throw new \WappointmentException('Calendar already connected');
        }
        if (count($calurls) > 3) {
            throw new \WappointmentException('Cannot connect more than 4 calendars');
        }

        $calurls[md5($calurl)] = $calurl;

        WPHelpers::setStaffOption('cal_urls', $calurls);

        return ['message' => 'Calendar has been recorded'];
    }

    protected static function calurlValid($value)
    {
        // 1 is url
        $validator = new \Rakit\Validation\Validator;

        $validation = $validator->validate(['calurl' => $value], [
            'calurl' => 'required|url:http,https'
        ]);

        if ($validation->fails()) {
            throw new \WappointmentException('The URL is not valid');
        }

        // 2 is reachable
        $result = false;
        try {
            $result = (new \Wappointment\Services\Calendar($value, Settings::get('activeStaffId')))->refetch();
        } catch (\Exception $e) {
            throw new \WappointmentException($e->getMessage());
        }

        return $result;
    }

    public function disconnectCal(Request $request)
    {
        $calendar_id = $request->input('calendar_id');
        $calurls = WPHelpers::getStaffOption('cal_urls');
        if (empty($calendar_id) || empty($calurls[$calendar_id])) {
            throw new \WappointmentException("Cannot find calendar", 1);
        }
        //remove events
        \Wappointment\Models\Status::where('source', $calendar_id)->delete();

        //unset calendars
        unset($calurls[$calendar_id]);
        WPHelpers::setStaffOption('cal_urls', $calurls);
        $calendar_logs = WPHelpers::getStaffOption('calendar_logs');
        if (isset($calendar_logs[$calendar_id])) {
            unset($calendar_logs[$calendar_id]);
            WPHelpers::setStaffOption('calendar_logs', $calendar_logs);
        }

        $result = [];
        $result['calendar_url'] = $calurls;
        $result['calendar_logs'] = $calendar_logs;
        $result['message'] = 'Calendar has been disconnected';
        return $result;
    }

    public function refreshCalendars(Request $request)
    {
        \Wappointment\System\Scheduler::syncCalendar();

        return [
            'calendar_url' => WPHelpers::getStaffOption('cal_urls'),
            'calendar_logs' => WPHelpers::getStaffOption('calendar_logs'),
            'message' => 'Calendars refreshed',
        ];
    }
}
