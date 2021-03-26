<?php

namespace Wappointment\Installation;

use Wappointment\Config\Database;
use Wappointment\ClassConnect\Capsule;
use Wappointment\System\Status;

class MigrateHasServices extends Migrate
{
    public function hasMultiService()
    {
        return Status::dbVersionAlterRequired()
            && Capsule::schema()->hasTable(Database::$prefix_self . '_services')
            && Capsule::schema()->hasTable(Database::$prefix_self . '_locations')
            && Capsule::schema()->hasTable(Database::$prefix_self . '_service_location')
            && defined('WAPPOINTMENT_SERVICES_VERSION');
    }
}
