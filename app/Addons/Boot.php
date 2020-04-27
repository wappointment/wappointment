<?php

namespace Wappointment\Addons;

interface Boot
{

    public static function init();
    public static function isInstalled();
    public static function isValid();

    public static function installedFilters();

    public static function getMainSettings($data);

    public static function hooksAndFiltersAlways();

    public static function adminInit();

    public static function registerAddon($addons);

    public static function jsVariables($var);
}
