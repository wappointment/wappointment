<?php

namespace Wappointment\Services;

use Wappointment\Managers\Central;
use Wappointment\ClassConnect\RakitValidator;
use Wappointment\Validators\HasValues;
use Wappointment\Validators\RequiredIfHas;
use Wappointment\Validators\RequiredIfFields;

class Calendars
{
    public static function getModel()
    {
        return Central::get('CalendarModel')::model();
    }

    public static function all()
    {
        $services = static::getModel()::orderBy('sorting')->fetch();
        return $services->filter(function ($service, $key) {
            return count($service->locations) > 0;
        })->all();
    }

    public static function save($serviceData)
    {
        $validator = new RakitValidator;
        $validation_messages = [
            'locations_id' => 'Please select how do you deliver the service',
        ];
        $validator->setMessages(apply_filters('wappointment_service_validation_messages', $validation_messages));
        $validator->addValidator('hasvalues', new HasValues());
        $validator->addValidator('required_if_has', new RequiredIfHas());
        $validator->addValidator('required_if_fields', new RequiredIfFields());

        $validationRules = [
            'name' => 'required',
            'options' => '',
            'options.durations' => 'required|array',
            'options.durations.*.duration' => 'required|numeric',
            'locations_id' => 'required|array',
        ];

        $validationRules = apply_filters('wappointment_service_validation_rules', $validationRules);
        $validation = $validator->make($serviceData, $validationRules);

        $validation->validate();

        if ($validation->fails()) {
            throw new \WappointmentValidationException("Cannot save Calendar", 1, null, $validation->errors()->toArray());
            return $validation->errors()->toArray();
        }

        return static::saveService($serviceData);
    }

    public static function saveService($serviceData)
    {
        $serviceDB = null;
        if (!empty($serviceData['id'])) {
            $serviceDB = static::getModel()::findOrFail($serviceData['id']);
        } else {
            if (!static::getModel()::canCreate()) {
                throw new \WappointmentValidationException("Cannot save Calendar");
            }
        }

        $serviceData = apply_filters('wappointment_service_before_saved', $serviceData, $serviceDB);

        if (!empty($serviceDB)) {
            $serviceDB->update($serviceData);
        } else {
            $serviceDB = static::getModel()::create($serviceData);
        }
        if (!empty($serviceData['locations_id'])) {
            $serviceDB->locations()->sync($serviceData['locations_id']);
        }

        do_action('wappointment_service_saved', $serviceData);
        return $serviceDB;
    }


    public static function delete($service_id = false)
    {
        $serviceModel = static::getModel();
        $old_service = $serviceModel::find($service_id);
        $serviceModel::where('sorting', '>', $old_service->sorting)->decrement('sorting');
        return $serviceModel::where('id', $service_id)->delete();
    }

    public static function getService($service = false, $service_id = false)
    {
        return self::get($service_id);
    }

    public static function get($service_id = false)
    {
        return static::getModel()::findOrFail($service_id);
    }

    public static function total()
    {
        return static::getModel()::where('id', '>', 0)->count();
    }

    public static function reorder($id, $neworder)
    {
        $serviceModel = static::getModel();
        $old_service = $serviceModel::find($id);
        if ($old_service->sorting > $neworder) {
            $serviceModel::where('sorting', '>=', $neworder)->where('sorting', '<', $old_service->sorting)->increment('sorting');
        } else {
            $serviceModel::where('sorting', '>', $old_service->sorting)->where('sorting', '<=', $neworder)->decrement('sorting');
        }

        return $serviceModel::where('id', $id)->update(['sorting' => $neworder]);
    }
}
