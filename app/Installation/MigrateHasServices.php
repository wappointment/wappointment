<?php

namespace Wappointment\Installation;

use Wappointment\Config\Database;
use Wappointment\ClassConnect\Capsule;

class MigrateHasServices extends Migrate
{
    public function hasMultiService()
    {
        return Capsule::schema()->hasTable(Database::$prefix_self . '_services') && defined('WAPPOINTMENT_SERVICES_VERSION');
    }
}
