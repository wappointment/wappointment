<?php

namespace Wappointment\System;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Settings;
use Wappointment\Services\VersionDB;
use Wappointment\Services\Flag;

class Status
{
    public static $version = WAPPOINTMENT_VERSION;
    private static $last_step = 4;
    private static $db_version_required = '2.3.0';

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

    public static function dbVersion()
    {
        return WPHelpers::getOption('db_version');
    }

    public static function canSeeUpdatePage()
    {
        return !static::seenUpdatePage();
    }

    public static function seenUpdatePage()
    {
        $update_seen = static::viewedUpdates();
        return $update_seen && version_compare($update_seen, WAPPOINTMENT_VERSION) >= 0;
    }

    public static function getBaseVersion()
    {
        return substr(WAPPOINTMENT_VERSION, 0, 3 - strlen(WAPPOINTMENT_VERSION));
    }

    public static function hasPendingUpdates()
    {
        return static::coreRequiresDBUpdate() || static::addonRequiresDBUpdate();
    }

    public static function coreRequiresDBUpdate()
    {
        return version_compare(self::dbVersion(), self::$db_version_required) < 0;
    }

    public static function addonRequiresDBUpdate()
    {
        $addons_updates = apply_filters('wappointment_addons_requires_update', []);
        return !empty($addons_updates) ? $addons_updates : false;
    }

    public static function dotComNotSetYet()
    {
        if (VersionDB::atLeast(VersionDB::CAN_CREATE_SERVICES)) {
            return empty(Settings::getStaff('dotcom')) && empty(Flag::get('dotcomSet'));
        } else {
            return empty(Settings::getStaff('dotcom'));
        }
    }

    public static function hasMessages()
    {
        //test if zoom is used and no account is connected
        $messages = [];
        if (static::dotComNotSetYet()) {
            $services = \Wappointment\Managers\Service::all();

            foreach ($services as $service) {
                if (\Wappointment\Managers\Service::hasZoom($service)) {
                    $messages[] = [
                        'message' => 'Hey! You are using Video meetings, great for you! Generate meetings automatically with Zoom, Google meet etc ... by connecting these services',
                        'link' => [
                            'label' => 'Connect Account',
                            'address' => '[goto_calendars_zoom_account]'
                        ]
                    ];
                    break;
                }
            }
        }
        $messagesOld = \Wappointment\WP\Alerts::get();
        if (!empty($messagesOld)) {
            foreach ($messagesOld as $messageOld) {
                $messages[] = [
                    'message' => $messageOld,
                ];
            }
        }

        return $messages;
    }

    public static function dbVersionUpdateComplete()
    {
        return WPHelpers::setOption('db_version', self::$db_version_required);
    }

    public static function dbVersionOnCreation()
    {
        return WPHelpers::setOption('db_version_created', self::$db_version_required);
    }

    public static function dbVersionAlterRequired()
    {
        return version_compare(WPHelpers::getOption('db_version_created', '1.0.0'), self::$db_version_required) < 0;
    }

    public static function setViewedUpdated()
    {
        return WPHelpers::setStaffOption(
            'viewed_updates',
            WAPPOINTMENT_VERSION,
            false,
            true
        );
    }

    public static function viewedUpdates()
    {
        return WPHelpers::getStaffOption('viewed_updates');
    }


    public static function wizardStep()
    {
        return (int) WPHelpers::getOption('wizard_step');
    }

    public static function wizardComplete()
    {
        return (int) WPHelpers::getOption('wizard_step') < 0
            || (int) WPHelpers::getOption('wizard_step') == self::$last_step;
    }
}
