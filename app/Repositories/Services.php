<?php

namespace Wappointment\Repositories;

use Wappointment\Managers\Central;

class Services extends AbstractRepository
{
    use MustRefreshAvailability;

    public $cache_key = 'services';

    public function query()
    {
        $result = Central::get('ServiceModel')::orderBy('sorting')->fetch();
        $this->refreshAvailability();
        return $result;
    }
}
