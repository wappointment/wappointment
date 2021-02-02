<?php

namespace Wappointment\ClassConnect;

use Wappointment\Services\VersionDB;

if (VersionDB::atLeast(VersionDB::CAN_DEL_CLIENT)) {
    trait ClientSoftDeletes
    {
        use SoftDeletes;
    }
} else {
    trait ClientSoftDeletes
    {
    }
}
