<?php

namespace Wappointment\Installation;

use Wappointment\WP\Helpers as WPHelpers;

class Process extends AbstractProcess
{

    protected $key = 'installation_step';
    protected $steps = [
        'Wappointment\Installation\Steps\CreateMigrationTable',
        'Wappointment\Installation\Steps\CreateTables',
        true
    ];

    protected function isUpToDate()
    {
        if (empty(WPHelpers::getOption('installation_completed'))) return false;
        return true;
    }

    protected function completed()
    {
        (new \Wappointment\WP\CustomPage())->install();

        $this->complete = true;

        \Wappointment\Services\Settings::save('activeStaffId', WPHelpers::userId());
        \Wappointment\Services\Settings::save('email_notifications', WPHelpers::currentUserEmail());
        \Wappointment\System\Status::dbVersionUpdateComplete();
        return WPHelpers::setOption('installation_completed', (int) current_time('timestamp'), true);
    }
}
