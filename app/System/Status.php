<?php

namespace Wappointment\System;

use Wappointment\WP\Helpers as WPHelpers;

class Status
{
    public static $version = WAPPOINTMENT_VERSION;
    private static $last_step = 4;

    public static function isInstalled()
    {
        return (bool)self::installationTime();
    }

    public static function installationTime()
    {
        return WPHelpers::getOption('installation_completed');
    }

    public static function hasPendingUpdates()
    {
        return false;
    }

    public static function wizardStep()
    {
        return (int)WPHelpers::getOption('wizard_step');
    }

    public static function wizardComplete()
    {
        return (int)WPHelpers::getOption('wizard_step') < 0 || (int)WPHelpers::getOption('wizard_step') == self::$last_step;
    }

    public static function isReadyForUpdatePage()
    {
        return (bool)WPHelpers::getOption('update_page') === false ? false : true;
    }
}
