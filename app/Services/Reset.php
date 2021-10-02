<?php

namespace Wappointment\Services;

use Wappointment\Config\Database;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\WP\Scheduler as WPScheduler;
use Wappointment\ClassConnect\Capsule;
use Wappointment\Services\Wappointment\DotCom;
use Wappointment\Repositories\Availability;
use Wappointment\Repositories\CalendarsBack;
use Wappointment\Repositories\Services;

class Reset
{
    private $options = [
        'flags',
        'wizard_step',
        'subscribed_status',
        'widget_settings',
        'site_details',
        'site_key',
        'installation_completed',
        'installation_step',
        'db_version_created',
        'db_version',
        'appointments_must_refresh',
        'appointments_update'
    ];

    private $user_options = [
        'availability',
        'calendar_logs',
        'cal_urls',
        'viewed_updates',
        'hello_page',
        'preferences',
        'since_last_refresh',
    ];

    private $db_drop = [
        'appointments',
        'calendars',
        'calendar_service',
        'clients',
        'failed_jobs',
        'jobs',
        'locations',
        'logs',
        'migrations',
        'orders',
        'order_price',
        'prices',
        'reminders',
        'services',
        'service_location',
        'statuses',
    ];

    public function __construct()
    {

        do_action('wappointment_reset');
        sleep(2); //giving time for revert on addons
        static::eraseCache();
        $this->dotComInforms();

        $this->removeStaffSettings();
        $this->dropTables();
        $this->removeCoreSettings();

        WPScheduler::clearScheduler();
    }

    private function dotComInforms()
    {
        $dotcomapi = new DotCom;
        $dotcomapi->setStaff();
        $dotcomapi->notifyReset();
    }

    private function removeStaffSettings()
    {
        foreach (Staff::getIds() as $staff_id) {
            Settings::deleteStaff($staff_id);
            foreach ($this->user_options as $option_key) {
                WPHelpers::deleteStaffOption($option_key, $staff_id);
            }
        }
    }


    public function dropTables()
    {
        Capsule::schema()->disableForeignKeyConstraints();
        $db_list = [];
        foreach ($this->db_drop as $table_name) {
            $db_list[] = Database::$prefix_self . '_' . $table_name;
        }

        foreach (apply_filters('wappointment_db_drop', $db_list) as $table_name) {
            $full_table = Database::getWpSitePrefix() . $table_name;
            global $wpdb;
            $wpdb->query("DROP TABLE IF EXISTS $full_table");
        }
        Capsule::schema()->enableForeignKeyConstraints();
    }

    private function removeCoreSettings()
    {
        foreach ($this->options as $option_key) {
            WPHelpers::deleteOption($option_key);
        }

        Settings::delete();
    }

    public static function refreshCache()
    {
        (new CalendarsBack)->refresh();
        (new Services)->refresh();
        (new Availability)->refresh();
    }

    public static function eraseCache()
    {
        (new CalendarsBack)->clear();
        (new Services)->clear();
        (new Availability)->clear();
    }
}
