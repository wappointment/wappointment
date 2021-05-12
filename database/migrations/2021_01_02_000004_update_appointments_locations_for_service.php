<?php


use Wappointment\Models\Appointment;
use Wappointment\Models\Location;
use Wappointment\Services\Services;

class UpdateAppointmentsLocationsForService extends Wappointment\Installation\MigrateHasServices
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($this->hasMultiService()  && count(Services::all()) > 0) {
            return;
        }

        $reflectionAppointment = new ReflectionClass('\\Wappointment\\Models\\Appointment');
        foreach ($this->generateLocationIds() as $type => $location) {
            Appointment::where('type', $reflectionAppointment->getConstant('TYPE_' . strtoupper($type)))->update(['location_id' => $location->id]);
        }
    }

    private function generateLocationIds()
    {
        $service_free = \Wappointment\Services\Service::getObject();
        $types_id = [];
        foreach ($this->getDefaultLocations() as $type_key => $typeData) {
            $types_id[$type_key] = Location::create([
                'name' => $typeData['label'],
                'type' => $typeData['type'],
                'options' => $this->getLocationOptions($type_key, $service_free->service)
            ]);
        }

        return $types_id;
    }

    private function getDefaultLocations()
    {
        $widgetSettings = (new \Wappointment\Services\WidgetSettings)->get();
        return [
            'zoom' => ['label' => $widgetSettings['form']['byzoom'], 'type' => Location::TYPE_ZOOM],
            'physical' => ['label' => $widgetSettings['form']['inperson'], 'type' => Location::TYPE_AT_LOCATION],
            'phone' => ['label' => $widgetSettings['form']['byphone'], 'type' => Location::TYPE_PHONE],
            'skype' => ['label' => $widgetSettings['form']['byskype'], 'type' => Location::TYPE_SKYPE]
        ];
    }

    private function getLocationOptions($type, $service)
    {
        $new_options = ['type' => $type];

        if ($type == 'phone') {
            $new_options['countries'] = $service['options']['countries'];
        }
        if ($type == 'physical') {
            $new_options['address'] = $service['address'];
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
