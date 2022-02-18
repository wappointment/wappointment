<?php

namespace Wappointment\System;

use Wappointment\Repositories\CalendarsBack;
use Wappointment\Services\Settings;
use Wappointment\Services\VersionDB;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Calendars;
use Wappointment\Services\Availability;
use Wappointment\Services\Flag;
use Wappointment\Jobs\CleanPendingPaymentAppointment;
use Wappointment\Services\Recurrent;

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
            //TODO get only last 5 or 10 by updated_at asc
            foreach (Calendars::all(false, true) as $calendar) {
                (new Availability($calendar))->syncAndRegen();
            }
            (new CalendarsBack)->refresh();
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
        Flag::save('cronLastRun', time());
        if (Helpers::isProd()) {
            $lock = new \Wappointment\Services\Lock;
            if (!$lock->alreadySet()) {
                $lock->set();
                \Wappointment\Services\Queue::process();
                $lock->release();
            }
        } else {
            \Wappointment\Services\Queue::process();
        }
        static::checkDotCom();
        static::registerCleanPending();
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

    public static function registerCleanPending()
    {
        //Flag::save('CleanPending', false);
        if (!Flag::get('CleanPending')) {
            CleanPendingPaymentAppointment::registerJob();
            Flag::save('CleanPending', true);
        }
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
                foreach (Calendars::all(false, true) as $calendar) {
                    (new Availability($calendar))->regenerate();
                }
                (new CalendarsBack)->refresh();
            }
            (new Recurrent)->generate();
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
