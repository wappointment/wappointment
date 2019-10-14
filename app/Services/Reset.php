<?php

namespace Wappointment\Services;

use Wappointment\Config\Database;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\System\Installation;
use Wappointment\WP\Scheduler as WPScheduler;

class Reset
{
    public function __construct()
    {
        $this->removeStaffSettings();

        $this->dropTables();

        $this->removeCoreSettings();

        WPScheduler::clearScheduler();

        //$this->reInstallApplication();
    }

    private function removeStaffSettings()
    {
        foreach (Staff::getIds() as $staff_id) {
            Settings::deleteStaff($staff_id);
            WPHelpers::deleteStaffOption('availability', $staff_id);
            WPHelpers::deleteStaffOption('last-calendar-id', $staff_id);
            WPHelpers::deleteStaffOption('last-calendar-checked', $staff_id);
            WPHelpers::deleteStaffOption('last-calendar-parsed', $staff_id);
            WPHelpers::deleteStaffOption('viewed_updates', $staff_id);
            WPHelpers::deleteStaffOption('hello_page', $staff_id);
        }
    }


    private function dropTables()
    {
        \Illuminate\Database\Capsule\Manager::schema()->dropIfExists(Database::$prefix_self . '_appointments');
        \Illuminate\Database\Capsule\Manager::schema()->dropIfExists(Database::$prefix_self . '_statuses');
        \Illuminate\Database\Capsule\Manager::schema()->dropIfExists(Database::$prefix_self . '_reminders');
        \Illuminate\Database\Capsule\Manager::schema()->dropIfExists(Database::$prefix_self . '_clients');
        \Illuminate\Database\Capsule\Manager::schema()->dropIfExists(Database::$prefix_self . '_jobs');
        \Illuminate\Database\Capsule\Manager::schema()->dropIfExists(Database::$prefix_self . '_failed_jobs');
        \Illuminate\Database\Capsule\Manager::schema()->dropIfExists(Database::$prefix_self . '_logs');
        \Illuminate\Database\Capsule\Manager::schema()->dropIfExists(Database::$prefix_self . '_migrations');
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

        Settings::delete();
    }

    private function reInstallApplication()
    {
        new Installation();
    }
}
