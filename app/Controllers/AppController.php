<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Services\Wappointment\Feedback;

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

    public function sendFeedback(Request $request)
    {
        $feedback = new Feedback;
        $feedback->sendFeedback($request);

        return ['message' => 'Feedback sent', 'result' => true];
    }
}
