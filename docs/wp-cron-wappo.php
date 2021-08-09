<?php

/**
 * This file has been created for documentation purpose only
 * simply copy and modify the code for your own needs
 */

/**
 * This is in case your calendars are not synched properly or your emails are not processed out of the queue
 * This case scenario may happen if another WordPress plugin is running cron tasks that are crashing or blocking the other processes somehow
 */

if (!defined('ABSPATH')) {
    /** Set up WordPress environment */
    require_once __DIR__ . '/wp-load.php';
}

\Wappointment\System\Scheduler::syncCalendar();
\Wappointment\System\Scheduler::processQueue();

$last_scheduled = get_option('wappointment_last_daily_schedule');
$hour_now = (int)date('H');

if (
    ((empty($last_scheduled)) || (is_numeric($last_scheduled) && $last_scheduled + (3600 * 24) < time()))
    && $hour_now > 0 && $hour_now < 2
) {
    \Wappointment\System\Scheduler::dailyProcess();
    update_option('wappointment_last_daily_schedule', time());
}


die();
