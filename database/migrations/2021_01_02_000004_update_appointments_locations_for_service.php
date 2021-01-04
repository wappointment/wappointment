<?php


use Wappointment\Models\Appointment;
use Wappointment\Models\Location;

class UpdateAppointmentsLocationsForService extends Wappointment\Installation\MigrateHasServices
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
        $service_free = \Wappointment\Services\Service::getObject();
        $types_id = [];
        $widgetSettings = (new \Wappointment\Services\WidgetSettings)->get();
        $data = [];
        $data['skype'] = ['label' => $widgetSettings['form']['byskype'], 'type' => Location::TYPE_SKYPE];
        $data['phone'] = ['label' => $widgetSettings['form']['byphone'], 'type' => Location::TYPE_PHONE];
        $data['physical'] = ['label' => $widgetSettings['form']['inperson'], 'type' => Location::TYPE_AT_LOCATION];
        $data['zoom'] = ['label' => $widgetSettings['form']['byzoom'], 'type' => Location::TYPE_ZOOM];
        foreach ($data as $type_key => $typeData) {
            $types_id[$type_key] = Location::create([
                'name' => $typeData['label'],
                'type' => $typeData['type'],
                'options' => $this->getOptions($type_key, $service_free->service)
            ]);
        }

        $reflectionAppointment = new ReflectionClass('\\Wappointment\\Models\\Appointment');
        foreach ($types_id as $type => $location) {
            Appointment::where('type', $reflectionAppointment->getConstant('TYPE_' . strtoupper($type)))->update(['location_id' => $location->id]);
        }
    }

    protected function getOptions($type, $service)
    {
        $new_options = ['type' => $type];
        switch ($type) {
            case 'phone':
                $new_options['countries'] = $service['options']['countries'];
                break;
            case 'physical':
                $new_options['address'] = $service['address'];
                break;
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
