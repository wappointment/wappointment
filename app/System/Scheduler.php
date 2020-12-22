<?php

namespace Wappointment\System;

use Wappointment\Services\Settings;
use Wappointment\WP\Helpers as WPHelpers;

/**
 * TODO Most of this class is static but it has a constructor, review
 */
class Scheduler
{
    public function __construct()
    {
        \Wappointment\WP\Scheduler::init();
    }

    /**
     * runs every 5 min
     *
     * @return void
     */
    public static function syncCalendar()
    {
        foreach (\Wappointment\Services\Staff::getIds() as $staff_id) {
            $calendar_urls = self::getUrlsToScan($staff_id);
            $hasChanged = false;
            if (!empty($calendar_urls)) {
                foreach ($calendar_urls as $calurl) {
                    if ((new \Wappointment\Services\Calendar($calurl, $staff_id))->fetch()) {
                        $hasChanged = true;
                    }
                }
            }

            //regenerate availability only when we get new events
            if ($hasChanged) {
                self::regenerateAvailability();
            }
        }
    }


    /**
     * Runs every minute
     * refactor as it contains non prod code
     *
     * @return void
     */
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

        static::checkDotCom();
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public static function checkDotCom()
    {
        (new \Wappointment\Services\Wappointment\DotCom)->checkForUpdates();
    }

    /**
     * Runs every hour for jobs that were processed but didn't complete
     *
     * @return void
     */
    public static function checkLostReservedJobs()
    {
        \Wappointment\Services\Queue::resetTimedoutJobs();
    }
    /**
     * Runs every day
     *
     * @return void
     */
    public static function dailyProcess()
    {
        try {
            self::regenerateAvailability(); // we at least regenerate once a day to avoid empty calendar after aa while without a booking
            self::checkLicence();
        } catch (\Exception $e) {
            //silent execution
        }
    }


    private static function regenerateAvailability()
    {
        (new \Wappointment\Services\Availability())->regenerate();
    }

    private static function checkLicence()
    {
        (new \Wappointment\Services\Wappointment\Licences)->check();
    }


    private static function getUrlsToScan($staff_id)
    {
        $calendar_urls = WPHelpers::getStaffOption('cal_urls', $staff_id);
        return self::upgradingUrls($calendar_urls, $staff_id);
    }

    /**
     * TODO This is a legacy, it was an upgrade process probably no longer needed. or can be split
     *
     * @param array $calendar_urls
     * @param int $staff_id
     * @return array
     */
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
        if (!empty($calendar_urls)) {
            foreach ($calendar_urls as $key => $calurl) {
                if (empty($calurl)) {
                    unset($calendar_urls[$key]);
                    $require_save = true;
                }
            }
        }

        if ($require_save) {
            WPHelpers::setStaffOption('cal_urls', $calendar_urls, $staff_id);
        }

        return $calendar_urls;
    }
}
