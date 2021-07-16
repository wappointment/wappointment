<?php

namespace Wappointment\Services;

class CurrentUser
{

    public static function get()
    {
        $current_user = false;
        if ($current_user === false) {
            $current_user = wp_get_current_user();
        }
        return $current_user;
    }

    public static function id()
    {
        return static::get()->ID;
    }

    public static function calendarId()
    {
        $calendar = \Wappointment\Models\Calendar::where('wp_uid', static::id())->first();
        return $calendar->id;
    }

    public static function isAdmin()
    {
        return static::get()->has_cap('switch_themes') || static::get()->has_cap('wappointment_manager');
    }

    public static function canViewCalendar()
    {
        return static::hasCap('wappo_calendar_man');
    }

    public static function canCreateFreeBlock()
    {
        return static::hasCap('wappo_calendar_add_free');
    }
    public static function canCreateBusyBlock()
    {
        return static::hasCap('wappo_calendar_add_busy');
    }

    public static function hasCap($capability)
    {
        return static::isAdmin() || static::get()->has_cap($capability);
    }
}
