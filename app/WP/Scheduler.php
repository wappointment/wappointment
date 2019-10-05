<?php

namespace Wappointment\WP;

class Scheduler
{
    private static $scheduled_jobs = [
        'wappointment_process_daily' => [
            'frequency' => 'daily',
            'method' => 'dailyProcess'
        ],
        'wappointment_process_every_hour' => [
            'frequency' => 'wappointment_one_hour',
            'method' => 'checkLostReservedJobs'
        ],

        'wappointment_process_every_thirty_min' => [
            'frequency' => 'wappointment_thirty_min',
            'method' => 'checkPendingReminder'
        ],
        'wappointment_process_every_5min' => [
            'frequency' => 'wappointment_five_min',
            'method' => 'syncCalendar'
        ],
        'wappointment_process_every_min' => [
            'frequency' => 'wappointment_one_min',
            'method' => 'processQueue'
        ]
    ];
    private static $registered_frequencies = [
        'daily' => [
            'interval' => 86400,
            'display' => 'Daily',
            'noregister' => true
        ],
        'wappointment_one_hour' => [
            'interval' => 3600,
            'display' => 'Once every hour'
        ],
        'wappointment_thirty_min' => [
            'interval' => 1800,
            'display' => 'Once every 30 minute'
        ],
        'wappointment_five_min' => [
            'interval' => 300,
            'display' => 'Once every 5 minutes'
        ],
        'wappointment_one_min' => [
            'interval' => 60,
            'display' => 'Once every minute'
        ]
    ];

    /*
    default wp cron unreliable
    */
    public static function init()
    {
        add_filter('cron_schedules', ['\\Wappointment\\WP\\Scheduler', 'addFrequencies']);

        $time_record = time();
        //self::clearScheduler();
        foreach (self::$scheduled_jobs as $wappo_wp_scheduled_job => $scheduledObject) {
            $scheduled_time = wp_next_scheduled($wappo_wp_scheduled_job);
            $time_record++;
            if ($scheduled_time === false) {
                wp_schedule_event($time_record + self::getInterval($scheduledObject['frequency']), $scheduledObject['frequency'], $wappo_wp_scheduled_job);
            } elseif ($scheduled_time - \time() < -300) {
                $time_record++;
                /* if (!empty($_GET['testcron'])) {
                    echo 'rescheduled <br/>';
                    echo '<br>now ' . $time_record;
                    echo '<br>NEXT ' . ($time_record + self::getInterval($scheduledObject['frequency']));
                } */
                wp_clear_scheduled_hook($wappo_wp_scheduled_job);
                wp_schedule_event($time_record + self::getInterval($scheduledObject['frequency']), $scheduledObject['frequency'], $wappo_wp_scheduled_job);
            }


            //action to be run
            add_action($wappo_wp_scheduled_job, ['\\Wappointment\\System\\Scheduler', $scheduledObject['method']]);
            /* if (!empty($_GET['testcron'])) {
                echo 'now' . time();
                echo ' Runs at : ' . $scheduled_time . ' ' . $wappo_wp_scheduled_job . ($scheduled_time - time()) . '<br>';
            } */
        }
    }

    public static function getInterval($frequency)
    {
        return self::$registered_frequencies[$frequency]['interval'];
    }

    public static function addFrequencies($schedules)
    {
        foreach (self::$registered_frequencies as $frequency_key => $frequency_object) {
            if (empty($frequency_object->noregister)) $schedules[$frequency_key] = $frequency_object;
        }

        return $schedules;
    }

    public static function clearScheduler()
    {
        foreach (self::$scheduled_jobs as $frequency_key => $frequency_object) {
            if (empty($frequency_object->noregister)) wp_clear_scheduled_hook($frequency_key);
        }
    }
}
