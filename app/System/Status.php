<?php

namespace Wappointment\System;

use Wappointment\WP\Helpers as WPHelpers;

class Status
{
    public static $version = WAPPOINTMENT_VERSION;
    private static $last_step = 4;

    public static function isInstalled()
    {
        return (bool) self::installationTime();
    }

    public static function installationTime()
    {
        return WPHelpers::getOption('installation_completed');
    }

    public static function installedForXDays()
    {
        return round((time() - self::installationTime()) / (60 * 60 * 24));
    }

    public static function hasPendingUpdates()
    {
        return false;
    }

    public static function viewedUpdates()
    {
        return WPHelpers::getStaffOption('viewed_updates');
    }

    public static function helloPage()
    {
        return WPHelpers::getStaffOption('hello_page');
    }

    public static function wizardStep()
    {
        return (int) WPHelpers::getOption('wizard_step');
    }

    public static function wizardComplete()
    {
        return (int) WPHelpers::getOption('wizard_step') < 0 || (int) WPHelpers::getOption('wizard_step') == self::$last_step;
    }

    public static function updatePages()
    {
        $versions = ['1.1.1', '1.1.0'];
        $viewed_updates_until = self::viewedUpdates();
        $toView = [];
        foreach ($versions as $version) {
            if (empty($viewed_updates_until) || version_compare($version, $viewed_updates_until) > 0) {
                $toView[] = ['version' => $version, 'changes' => call_user_func(get_called_class() . '::' . 'version' . str_replace('.', '_', $version))];
            } else {
                return $toView;
            }
        }
        return $toView;
    }

    public static function version1_1_0()
    {
        return [
            [
                'title' => 'Change staff providing services',
                'img' => 'change_staff.gif'
            ],
            [
                'title' => 'Change staff picture',
                'img' => 'change_staff_picture.gif'
            ],
            [
                'title' => 'Calendar back to staff\'s Timezone',
                'img' => 'calendar_back_staff_timezone.gif'
            ]
        ];
    }
    public static function version1_1_1()
    {
        return [
            [
                'title' => 'Bug fix',
                'img' => 'change_staff.gif'
            ],
        ];
    }
}
