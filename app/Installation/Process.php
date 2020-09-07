<?php

namespace Wappointment\Installation;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Settings;
use Wappointment\System\Status;

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
        if (empty(WPHelpers::getOption('installation_completed'))) {
            return false;
        }
        return true;
    }

    protected function completed()
    {
        (new \Wappointment\WP\CustomPage())->install();

        $this->complete = true;

        $mail_config = Settings::get('mail_config');
        $mail_config['wpmail_html'] = true;

        Settings::saveMultiple([
            'mail_config' => $mail_config,
            'show_welcome' => true,
            'activeStaffId' => (string)WPHelpers::userId(),
            'email_notifications' => WPHelpers::currentUserEmail(),
        ]);
        Status::dbVersionUpdateComplete();
        Status::setViewedUpdated();
        return WPHelpers::setOption('installation_completed', (int) current_time('timestamp'), true);
    }
}
