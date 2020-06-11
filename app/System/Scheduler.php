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
            $calendar_urls = self::getUrlsToScan($staff_id);
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

    private static function getUrlsToScan($staff_id)
    {
        $calendar_urls = WPHelpers::getStaffOption('cal_urls', $staff_id);
        return self::upgradingUrls($calendar_urls, $staff_id);
    }

    private static function upgradingUrls($calendar_urls, $staff_id)
    {
        $require_save = false;
        if (empty($calendar_urls)) {
            $calendar_url_old = Settings::getStaff('calurl', $staff_id);
            if (!empty($calendar_url_old)) {
                $calendar_urls[md5($calendar_url_old)] = $calendar_url_old;
                $require_save = true;
                Settings::saveStaff('calurl', false, $staff_id);
            }
        }

        foreach ($calendar_urls as $key => $calurl) {
            if (empty($calurl)) {
                unset($calendar_urls[$key]);
                $require_save = true;
            }
        }

        if ($require_save) {
            WPHelpers::setStaffOption('cal_urls', $calendar_urls, $staff_id);
        }

        return $calendar_urls;
    }

    public static function processQueue()
    {

        if (\WappointmentLv::isTest()) {
            \Wappointment\Services\Queue::process();
        } else {
            $lock = new \Wappointment\Services\Lock;
            if (!$lock->alreadySet()) {
                $lock->set();
                \Wappointment\Services\Queue::process();
                $lock->release();
            }
        }
    }

    public static function checkPendingReminder()
    {
    }

    public static function checkLostReservedJobs()
    {
        \Wappointment\Services\Queue::resetTimedoutJobs();
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
