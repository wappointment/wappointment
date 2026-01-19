<?php
declare(strict_types=1);

namespace Wappointment\Routes\Api;

use Wappointment\Controllers\Api\Page3Controller;

class Page3Route extends BaseRoute
{
    protected bool $cacheable = true;

    public function getRoute(): string
    {
        return '/page3';
    }

    public function getCallback(): callable
    {
        return new Page3Controller();
    }
}
