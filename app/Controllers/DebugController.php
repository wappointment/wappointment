<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Reset;
use Wappointment\Services\ViewsData;

class DebugController extends RestController
{
    public function freshInstall()
    {
        new Reset;

        return ['message' => 'Plugin has been fully reseted.'];
    }

    public function updatePage()
    {
        (new \Wappointment\WP\CustomPage())->makeEditablePage();
        return (new ViewsData())->load('settingsadvanced');
    }

    public function refreshCache()
    {
        Reset::clearCache();
        return ['message' => 'Cache has been reseted'];
    }
}
