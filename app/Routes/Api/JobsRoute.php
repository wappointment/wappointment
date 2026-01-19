<?php
declare(strict_types=1);

namespace Wappointment\Routes\Api;

use Wappointment\Controllers\Api\JobsController;

class JobsRoute extends BaseRoute
{
    protected bool $cacheable = false;

    public function getRoute(): string
    {
        return '/jobs';
    }

    public function getCallback(): callable
    {
        return new JobsController();
    }
}
