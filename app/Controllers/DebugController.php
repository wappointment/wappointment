<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Permissions;
use Wappointment\Services\Reset;
use Wappointment\Services\ViewsData;
use Wappointment\Services\BigPrice;

class DebugController extends RestController
{
    public function freshInstall()
    {
        (new Reset)->proceed();

        return ['message' => 'Plugin has been fully reseted.'];
    }

    public function updatePage()
    {
        (new \Wappointment\WP\CustomPage())->makeEditablePage();
        return (new ViewsData())->load('settingsadvanced');
    }

    public function refreshCache()
    {
        Reset::refreshCache();
        return ['message' => __('Cache has been reseted', 'wappointment')];
    }

    public function addManagerRole()
    {
        $perms = new Permissions;
        $perms->registerRole('wappointment_manager');

        return (new ViewsData())->load('settingsadvanced');
    }

    public function addBigPrice()
    {
        $bigPrice = new BigPrice;
        $bigPrice->setup();

        return (new ViewsData())->load('settingsadvanced');
    }
    
}
