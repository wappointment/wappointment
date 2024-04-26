<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Services\Health;
use Wappointment\Services\Permissions;
use Wappointment\Services\Wappointment\Feedback;
use Wappointment\System\Status;
// @codingStandardsIgnoreFile
class AppController extends RestController
{
    public function migrate(Request $request)
    {
        if (Status::coreRequiresDBUpdate()) {
            return $this->runUpdatesCore();
        }

        return $this->runUpdatesAddons();
    }

    public function health()
    {
        $perms = new Permissions;
        $perms->refreshStaffCap();

        return (new Health)->get();
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
        $addonsRequiringUpdate = Status::addonRequiresDBUpdate();
        if (!$addonsRequiringUpdate) {
            throw new \WappointmentException("There is no update to run", 1);
        }
        foreach ($addonsRequiringUpdate as $addon_details) {
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
