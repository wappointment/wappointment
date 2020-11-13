<?php

namespace Wappointment\ClassConnect;

use Wappointment\System\Status;

if (version_compare(Status::dbVersion(), '1.9.3') >= 0) {
    trait ClientSoftDeletes
    {
        use SoftDeletes;
    }
} else {
    trait ClientSoftDeletes
    {
    }
}
