<?php

namespace Wappointment\Services;

use Wappointment\Config\Database;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\WP\Scheduler as WPScheduler;
use Wappointment\ClassConnect\Capsule;
use Wappointment\Services\Wappointment\DotCom;

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

    public function __construct()
    {

        do_action('wappointment_reset');
        sleep(2); //giving time for revert on addons

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


    private function dropTables()
    {
        $migrate = new \Wappointment\Installation\Migrate();
        $migrate->rollback();

        Capsule::schema()->dropIfExists(Database::$prefix_self . '_migrations');
        if (Capsule::schema()->hasTable(Database::$prefix_self . '_migrations')) {
            throw new \WappointmentException("Error while DROPPING DB tables", 1);
        }
    }

    private function removeCoreSettings()
    {
        foreach ($this->options as $option_key) {
            WPHelpers::deleteOption($option_key);
        }

        Settings::delete();
    }
}
