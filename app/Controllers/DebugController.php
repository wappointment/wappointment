<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Reset;

class DebugController extends RestController
{
    public function freshInstall()
    {
        new Reset();

        return ['message' => 'Plugin has been fully reseted.'];
    }
}
