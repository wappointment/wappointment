<?php

namespace Wappointment\Services;

use Wappointment\Models\Service as ServiceModel;
use Wappointment\Services\ServiceInterface;
use Wappointment\ClassConnect\RakitValidator;
use Wappointment\Validators\HasValues;
use Wappointment\Validators\RequiredIfHas;
use Wappointment\Validators\RequiredIfFields;
use Wappointment\Models\Location as LocationModel;

class Services implements ServiceInterface
{
    public static function all()
    {
        $services = ServiceModel::orderBy('sorting')->fetch();
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

        $serviceData = static::clearDeletedDurations($serviceData);
        $validation->validate();

        if ($validation->fails()) {
            throw new \WappointmentValidationException("Cannot save Service", 1, null, $validation->errors()->toArray());
            return $validation->errors()->toArray();
        }

        return static::saveService($serviceData);
    }

    public static function clearDeletedDurations($serviceData)
    {
        $durations = [];
        foreach ($serviceData['options']['durations'] as $key => $durationObj) {
            if (empty($durationObj['delete'])) {
                $durationObj['duration'] = (int) $durationObj['duration'];
                $durations[] = $durationObj;
            }
        }

        $serviceData['options']['durations'] = $durations;
        return $serviceData;
    }

    public static function saveService($serviceData)
    {
        $serviceDB = null;
        if (!empty($serviceData['id'])) {
            $serviceDB = ServiceModel::find($serviceData['id']);
        } else {
            if (!ServiceModel::canCreate()) {
                throw new \WappointmentValidationException("Cannot save Services");
            }
        }

        $serviceData = apply_filters('wappointment_service_before_saved', $serviceData, $serviceDB);

        if (!empty($serviceDB)) {
            $serviceDB->update($serviceData);
        } else {
            $serviceDB = ServiceModel::create($serviceData);
        }
        if (!empty($serviceData['locations_id'])) {
            $serviceDB->locations()->sync($serviceData['locations_id']);
        }

        do_action('wappointment_service_saved', $serviceData);
        return $serviceDB;
    }

    public static function patch($service_id, $data)
    {
        $serviceDB = null;
        if (!empty($service_id)) {
            $serviceDB = ServiceModel::find($service_id);
        }
        if (empty($serviceDB)) {
            throw new \WappointmentException("Error patching service (service suite)", 1);
        }
        $options = array_merge($serviceDB->options, $data['options']);
        $serviceDB->update(['options' => $options]);
    }

    public static function delete($service_id = false)
    {
        $old_service = ServiceModel::find($service_id);
        ServiceModel::where('sorting', '>', $old_service->sorting)->decrement('sorting');
        return ServiceModel::where('id', $service_id)->delete();
    }

    public static function getService($service = false, $service_id = false)
    {
        return self::get($service_id);
    }

    public static function get($service_id = false)
    {
        return ServiceModel::find($service_id);
    }

    public static function getObject($service_id = false)
    {
        return new \Wappointment\Decorators\Service(static::get($service_id));
        return new \WappointmentAddonServices\Decorators\Service(static::get($service_id));
    }

    public static function total()
    {
        return ServiceModel::where('id', '>', 0)->count();
    }

    public static function hasZoom($service)
    {
        foreach ($service->locations as $key => $location) {
            if ($location->type === LocationModel::TYPE_ZOOM) {
                return true;
            }
        }

        return false;
    }

    public static function reorder($id, $neworder)
    {
        $old_service = ServiceModel::find($id);
        if ($old_service->sorting > $neworder) {
            ServiceModel::where('sorting', '>=', $neworder)->where('sorting', '<', $old_service->sorting)->increment('sorting');
        } else {
            ServiceModel::where('sorting', '>', $old_service->sorting)->where('sorting', '<=', $neworder)->decrement('sorting');
        }

        return ServiceModel::where('id', $id)->update(['sorting' => $neworder]);
    }
}
