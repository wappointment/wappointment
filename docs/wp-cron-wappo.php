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
