<?php

namespace Wappointment\System;

use Wappointment\Services\Settings;
use Wappointment\WP\Helpers as WPHelpers;

class Scheduler
{
    public function __construct()
    {
        switch ((int) Settings::get('scheduler_mode')) {
            case 1:
                $this->setWappointmentScheduler();
                break;
            default:
                \Wappointment\WP\Scheduler::init();
        }
    }

    public static function syncCalendar()
    {

        foreach (\Wappointment\Services\Staff::getIds() as $staff_id) {
            $calendar_urls = WPHelpers::getStaffOption('cal_urls');
            if (empty($calendar_urls)) {
                $calendar_url_old = Settings::getStaff('calurl', $staff_id);
                $calendar_urls[md5($calendar_url_old)] = $calendar_url_old;
                WPHelpers::setStaffOption('cal_urls', $calendar_urls);
                Settings::saveStaff('calurl', false);
            }
            $hasChanged = false;
            foreach ($calendar_urls as $calurl) {
                if ((new \Wappointment\Services\Calendar($calurl, $staff_id))->fetch()) {
                    $hasChanged = true;
                }
            }
            //regenerate availability only when we get new events
            if ($hasChanged) {
                (new \Wappointment\Services\Availability())->regenerate();
            }
        }
    }

    public static function processQueue()
    {
        \Wappointment\Services\Queue::process();
    }
    public static function checkPendingReminder()
    {
    }

    public static function checkLostReservedJobs()
    {
        \Wappointment\Services\Queue::lostReserved();
    }

    public static function dailyProcess()
    {
        try {
            self::checkLicence();
        } catch (\Exception $e) {
            //silent execution
        }
    }
    public static function checkLicence()
    {
        (new \Wappointment\Services\Wappointment\Licences)->check();
    }
    private function setWappointmentScheduler()
    {
    }
}
