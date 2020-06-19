<?php

namespace Wappointment\Addons;

interface Boot
{
    public static function isValid();
    public static function installedFilters();
}
