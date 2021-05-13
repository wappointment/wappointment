<?php

namespace Wappointment\Repositories;

use Wappointment\Managers\Central;
use Wappointment\Services\Settings;

class Services extends AbstractRepository
{
    use MustRefreshAvailability;

    public $cache_key = 'services';

    public function query()
    {
        $result = Central::get('ServiceModel')::orderBy('sorting')->fetch();
        $this->testIfsold($result);
        $this->refreshAvailability();
        return $result->toArray();
    }

    protected function testIfsold($services)
    {
        foreach ($services as $service) {
            if (!empty($service->options['woo_sellable'])) {
                Settings::save('services_sold', true);
                return;
            }
        }
    }
}
