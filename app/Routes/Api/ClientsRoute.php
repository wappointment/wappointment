<?php
declare(strict_types=1);

namespace Wappointment\Routes\Api;

use Wappointment\Controllers\Api\ClientsController;

class ClientsRoute extends BaseRoute
{
    protected bool $cacheable = false;

    public function getRoute(): string
    {
        return '/clients';
    }

    public function getCallback(): callable
    {
        return new ClientsController();
    }
}
