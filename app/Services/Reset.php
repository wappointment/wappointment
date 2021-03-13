<?php

namespace Wappointment\Services;

use Wappointment\Config\Database;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\WP\Scheduler as WPScheduler;
use Wappointment\ClassConnect\Capsule;
use Wappointment\Services\Wappointment\DotCom;

class Reset
{
    public function __construct()
    {

        do_action('wappointment_reset');
        sleep(2); //giving time for revert on addons

        //$this->dotComInforms();

        $this->removeStaffSettings();
        $this->dropTables();
        $this->removeCoreSettings();

        WPScheduler::clearScheduler();
    }

    private function dotComInforms()
    {
        $acs_id = Settings::get('activeStaffId');
        $dotcomapi = new DotCom;
        $dotcomapi->setStaff($acs_id);
        $dotcomapi->notifyReset();
    }

    private function removeStaffSettings()
    {
        foreach (Staff::getIds() as $staff_id) {
            Settings::deleteStaff($staff_id);
            WPHelpers::deleteStaffOption('availability', $staff_id);
            WPHelpers::deleteStaffOption('calendar_logs', $staff_id);
            WPHelpers::deleteStaffOption('cal_urls', $staff_id);
            WPHelpers::deleteStaffOption('viewed_updates', $staff_id);
            WPHelpers::deleteStaffOption('hello_page', $staff_id);
            WPHelpers::deleteStaffOption('preferences', $staff_id);
            WPHelpers::deleteStaffOption('since_last_refresh', $staff_id);
        }
    }


    private function dropTables()
    {
        $migrate = new \Wappointment\Installation\Migrate();
        try {
            $migrate->rollback();
        } catch (\Throwable $th) {
            throw new \WappointmentException("Error while DROPPING DB tables " . $th, 1);
        }

        Capsule::schema()->dropIfExists(Database::$prefix_self . '_migrations');
        if (Capsule::schema()->hasTable(Database::$prefix_self . '_migrations')) {
            throw new \WappointmentException("Error while DROPPING DB tables", 1);
        }
    }

    private function removeCoreSettings()
    {
        WPHelpers::deleteOption('wizard_step');
        WPHelpers::deleteOption('subscribed_status');

        WPHelpers::deleteOption('widget_settings');

        WPHelpers::deleteOption('site_details');
        WPHelpers::deleteOption('site_key');

        WPHelpers::deleteOption('installation_completed');
        WPHelpers::deleteOption('installation_step');

        WPHelpers::deleteOption('db_version');

        Settings::delete();
    }
}
