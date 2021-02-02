<?php

namespace Wappointment\Services;

use Wappointment\System\Status;

class VersionDB
{
    const CAN_DEL_CLIENT = '1.9.3';
    const CAN_CREATE_SERVICES = '2.1.0';

    public static function atLeast($version)
    {
        return version_compare(Status::dbVersion(), $version) >= 0;
    }

    public static function isLessThan($version)
    {
        return version_compare(Status::dbVersion(), $version) < 0;
    }

    public static function equal($version)
    {
        return version_compare(Status::dbVersion(), $version) === 0;
    }

    public static function canServices()
    {
        return static::atLeast(static::CAN_CREATE_SERVICES);
    }

    public static function canDelClient()
    {
        return static::atLeast(static::CAN_DEL_CLIENT);
    }
}
