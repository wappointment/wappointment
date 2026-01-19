<?php
declare(strict_types=1);

namespace Wappointment\Routes\Api;

use Wappointment\Controllers\Api\Page2Controller;

class Page2Route extends BaseRoute
{
    protected bool $cacheable = true;

    public function getRoute(): string
    {
        return '/page2';
    }

    public function getCallback(): callable
    {
        return new Page2Controller();
    }
}
