<?php
declare(strict_types=1);

namespace Wappointment\Routes\Api;

use Wappointment\Controllers\Api\Page1Controller;

class Page1Route extends BaseRoute
{
    protected bool $cacheable = true;

    public function getRoute(): string
    {
        return '/page1';
    }

    public function getCallback(): callable
    {
        return new Page1Controller();
    }
}
