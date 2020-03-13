<?php

namespace Wappointment\System;

use Wappointment\Services\Settings;

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
            $calendar_url = Settings::getStaff('calurl', $staff_id);
            if (!empty($calendar_url)) {
                (new \Wappointment\Services\Calendar($calendar_url, $staff_id))->fetch();
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
        (new \Wappointment\Services\Licences)->check();
    }
    private function setWappointmentScheduler()
    {
    }
}
