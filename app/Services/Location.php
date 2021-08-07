<?php

namespace Wappointment\Services;

use Wappointment\Models\Location as LocationModel;
use Wappointment\ClassConnect\RakitValidator;
use Wappointment\Managers\Service as ServiceManager;

class Location
{

    public static function save($locationData)
    {
        $validator = new RakitValidator;
        $validation_messages = [
            'type' => 'Please select the modality type',
            'options.address' => 'Enter an address',
            'options.countries' => 'Select countries which are callable',
            'options.video' => 'Select video provider',
        ];
        $validator->setMessages($validation_messages);

        $validationRules = [
            'name' => 'required|is_adv_string|max:100',
            'type' => 'required|numeric',
            'options' => '',
            'options.address' => 'required_if_has:type,' . LocationModel::TYPE_AT_LOCATION,
            'options.countries' => 'required_if_has:type,' . LocationModel::TYPE_PHONE . '|array',
            'options.video' => 'required_if_has:type,' . LocationModel::TYPE_ZOOM,
        ];

        $validation = $validator->make($locationData, $validationRules);

        $validation->validate();

        if ($validation->fails()) {
            throw new \WappointmentValidationException("Cannot save Modality", 1, null, $validation->errors()->toArray());
        }

        return static::saveLocation($locationData);
    }


    public static function saveLocation($locationData)
    {
        $serviceDB = null;
        if (!empty($locationData['id'])) {
            $serviceDB = LocationModel::findOrFail($locationData['id']);
        } else {
            if (!ServiceManager::model()::canCreate()) {
                throw new \WappointmentValidationException("Cannot save Modality");
            }
        }

        return !empty($serviceDB) ? $serviceDB->update($locationData) : LocationModel::create($locationData);
    }
}
