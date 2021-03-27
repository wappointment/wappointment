<?php

use Wappointment\Models\Appointment;
use Wappointment\Models\Location;
use Wappointment\Models\Service as ServiceModel;
use Wappointment\System\Status;

class ImportService extends Wappointment\Installation\MigrateHasServices
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($this->hasMultiService()) {
            return;
        }
        if (!Status::dbVersionAlterRequired()) {
            return;
        }
        $service_free = \Wappointment\Services\Service::getObject();

        \Wappointment\Services\Service::updateLocations($service_free->type, $service_free->options, $service_free->address);

        if ($service_free->service['name'] !== '') { //doesnt run for new installation just upgrades
            $serviceObj = ServiceModel::create([
                'name' => $service_free->service['name'],
                'options' => $this->getOptions($service_free->service),
            ]);

            $locations = Location::get();
            $serviceObj->locations()->attach($locations);

            Appointment::where('id', '>', 0)->update(['service_id' => $serviceObj->id]);
        }
    }
    /**
     * Restructuring the service's options
     * */
    protected function getOptions($service)
    {
        $new_options = [];
        $new_options['durations'] = [];
        $new_options['durations'][] = [
            'duration' => $service['duration']
        ];
        $new_options['fields'] = ['email', 'name'];
        $new_options['icon'] = "";
        foreach ($service['options'] as $key => $value) {
            if (!in_array($key, ['countries', 'woo_prod_id', 'woo_sellable'])) {
                $new_options['durations'][0][$key] = $value;
            } else {
                if ($key != 'countries') {
                    $new_options[$key] = $value;
                }
            }
        }

        return $new_options;
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
