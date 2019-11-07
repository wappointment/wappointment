<?php

namespace Wappointment\Services;

use Wappointment\Config\Database;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\WP\Scheduler as WPScheduler;
use Wappointment\ClassConnect\Capsule;

class Reset
{
    public function __construct()
    {

        do_action('wappointment_reset');

        $this->removeStaffSettings();

        $this->dropTables();

        $this->removeCoreSettings();

        WPScheduler::clearScheduler();
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
        $migrate = new \Wappointment\Installation\Migrate();
        $migrate->rollback();
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_migrations');
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
}
