<?php

namespace Wappointment\System;

use Wappointment\Services\Settings;
use Wappointment\Services\VersionDB;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Calendars;
use Wappointment\Services\Availability;

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
        if (!VersionDB::atLeast(VersionDB::CAN_CREATE_SERVICES)) {
            static::syncCalendarLegacy();
        } else {
            foreach (Calendars::all() as $calendar) {
                // dd('zero', $calendar->toArray());
                (new Availability($calendar))->syncAndRegen();
            }
        }
    }

    public static function syncCalendarLegacy()
    {
        $staff_id = Settings::get('activeStaffId');
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
            self::regenerateAvailabilityLegacy();
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
            if (!VersionDB::atLeast(VersionDB::CAN_CREATE_SERVICES)) {
                self::regenerateAvailability();
            } else {
                foreach (Calendars::all() as $calendar) {
                    (new Availability($calendar))->regenerate();
                }
            }
            // we at least regenerate once a day to avoid empty calendar after aa while without a booking
            self::checkLicence();
        } catch (\Exception $e) {
            //silent
        }
    }


    private static function regenerateAvailability()
    {
        //(new \Wappointment\Services\Availability())->regenerate();
    }

    private static function regenerateAvailabilityLegacy()
    {
        (new \Wappointment\Services\Availability())->regenerate();
    }

    private static function checkLicence()
    {
        (new \Wappointment\Services\Wappointment\Licences)->check();
    }
}
