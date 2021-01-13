<?php

namespace Wappointment\System;

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
            $calendar_urls = WPHelpers::getStaffOption('cal_urls', $staff_id);
            $hasChanged = false;
            if (!empty($calendar_urls) && is_array($calendar_urls)) {
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
        self::regenerateAvailability(); // we at least regenerate once a day to avoid empty calendar after aa while without a booking
        self::checkLicence();
    }


    private static function regenerateAvailability()
    {
        (new \Wappointment\Services\Availability())->regenerate();
    }

    private static function checkLicence()
    {
        (new \Wappointment\Services\Wappointment\Licences)->check();
    }
}
