<?php

namespace Wappointment\Services;

use Wappointment\Models\Location;
use Wappointment\Validators\HasValues;
use Wappointment\Validators\RequiredIfHas;

class Service implements ServiceInterface
{
    public static function save($serviceData)
    {
        $validator = new \Rakit\Validation\Validator;
        $validation_messages = [
            'type' => 'Please select how do you perform the service',
            'options.countries' => 'You need to select countries you will call for the phone service',
        ];
        $validator->setMessages(apply_filters('wappointment_service_validation_messages', $validation_messages));
        $validator->addValidator('hasvalues', new HasValues());
        $validator->addValidator('required_if_has', new RequiredIfHas());

        $validationRules = [
            'name' => 'required',
            'duration' => 'required|numeric',
            'type' => 'required|array|hasvalues:physical,phone,skype,zoom',
            'address' => 'required_if_has:type,physical',
            'options' => '',
            'options.countries' => 'required_if_has:type,phone|array',
            'options.video' => 'required_if_has:type,zoom',
        ];
        $validationRules = apply_filters('wappointment_service_validation_rules', $validationRules);
        $validation = $validator->make($serviceData, $validationRules);
        $validation->validate();

        if ($validation->fails()) {
            throw new \WappointmentValidationException(
                "Cannot save Service",
                1,
                null,
                $validation->errors()->toArray()
            );
            return $validation->errors()->toArray();
        }

        return static::saveService($serviceData);
    }

    public static function saveService($serviceData)
    {
        $service = static::get('service'); // to test the existing service

        $serviceData['options'] = array_merge($service['options'], $serviceData['options']);
        $serviceData = apply_filters('wappointment_service_before_saved', $serviceData, $service);
        //  return $serviceData;
        $resultSave = (bool) Settings::save('service', $serviceData);

        if (empty($service['name']) && empty($service['type'])) {
            self::createdService($serviceData['type']);
        }

        do_action('wappointment_service_saved', $serviceData);
        return $resultSave;
    }

    public static function patch($service_id, $data)
    {
        $serviceDB = static::get('service');
        $data['options'] = array_merge($serviceDB['options'], $data['options']);
        $serviceDB = array_merge($serviceDB, $data);
        Settings::save('service', $serviceDB);
    }

    public static function get($service_id = false)
    {
        return Settings::get('service');
    }
    public static function getObject($service_id = false)
    {
        return new \Wappointment\Decorators\Service(static::get());
    }

    public static function all()
    {
        return [static::get()];
    }

    public static function hasZoom($service)
    {
        return in_array('zoom', $service['type']);
    }

    private static function createdService($types)
    {
        foreach (Reminder::getSeeds($types) as $reminder) {
            Reminder::save($reminder);
        }
    }

    public static function updateLocations($types, $options, $address)
    {
        $typeId = [];
        foreach ($types as $type_name) {
            $typeId[] = static::getLocationTypeId($type_name);
        }
        $locations = Location::whereIn('type', $typeId)->get();
        $types = [];
        foreach ($locations as $location) {
            $optionsTemp = $location->options;
            if ($location->type == Location::TYPE_ZOOM) {
                $optionsTemp['video'] = $options['video'];
                $types[] = 'zoom';
            }
            if ($location->type == Location::TYPE_AT_LOCATION) {
                $optionsTemp['address'] = $address;
                $types[] = 'physical';
            }
            if ($location->type == Location::TYPE_PHONE) {
                $optionsTemp['countries'] = $options['countries'];
                $types[] = 'phone';
            }
            if ($location->type == Location::TYPE_SKYPE) {
                $types[] = 'skype';
            }
            $location->options = $optionsTemp;
            $location->save();
        }

        if (!Flag::get('remindersSaved')) {
            if (\Wappointment\Models\Reminder::count() < 1) {
                foreach (Reminder::getSeeds($types) as $reminder) {
                    Reminder::save($reminder);
                }
            }


            Flag::save('remindersSaved', true);
        }

        return $locations->map(function ($locationObj) {
            return $locationObj->id;
        });
    }

    public static function getLocationTypeId($type_name)
    {
        if ($type_name == 'skype') {
            return Location::TYPE_SKYPE;
        }
        if ($type_name == 'zoom') {
            return Location::TYPE_ZOOM;
        }
        if ($type_name == 'physical') {
            return Location::TYPE_AT_LOCATION;
        }
        if ($type_name == 'phone') {
            return Location::TYPE_PHONE;
        }
    }
}
