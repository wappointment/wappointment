<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;

class AppController extends RestController
{
    public function migrate(Request $request)
    {
        $migrator = new \Wappointment\Installation\Migrate;
        $migrated = $migrator->migrate();
        \Wappointment\System\Status::dbVersionUpdateComplete();
        if (!empty($migrated)) {
            \Wappointment\System\Status::dbVersionUpdateComplete();
            return ['message' => 'Database has been updated', 'migrated' => $migrated];
        }

        return ['message' => 'Database could not be updated', 'result' => false];
    }
}
