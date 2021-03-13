<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Services\Wappointment\Feedback;
use Wappointment\System\Status;

class AppController extends RestController
{
    public function migrate(Request $request)
    {
        if (Status::coreRequiresDBUpdate()) {
            return $this->runUpdatesCore();
        }

        return $this->runUpdatesAddons();
    }

    protected function runUpdatesCore()
    {
        $migrator = new \Wappointment\Installation\Migrate;
        $migrated = $migrator->migrate();
        if (!empty($migrated)) {
            Status::dbVersionUpdateComplete();
            return ['message' => 'Database has been updated', 'migrated' => $migrated];
        }
        throw new \WappointmentException("Database could not be updated", 1);
    }

    protected function runUpdatesAddons()
    {
        foreach (Status::addonRequiresDBUpdate() as $addon_details) {
            try {
                call_user_func($addon_details['namespace'] . '::runDbMigrate');
            } catch (\Throwable $th) {
                throw new \WappointmentValidationException("Could not update addon db", 1, null, [$th->getMessage()]);
            }
        }

        return ['message' => 'Database for addons has been updated'];
    }

    public function sendFeedback(Request $request)
    {
        $feedback = new Feedback;
        $feedback->sendFeedback($request);

        return ['message' => 'Feedback sent', 'result' => true];
    }
}
