<?php

namespace Wappointment\Services;

use Wappointment\Models\Location as LocationModel;
use Wappointment\Models\Service;
use Wappointment\ClassConnect\RakitValidator;
use Wappointment\Validators\HasValues;
use Wappointment\Validators\RequiredIfHas;
use Wappointment\Validators\RequiredIfFields;

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
        $validator->addValidator('hasvalues', new HasValues());
        $validator->addValidator('required_if_has', new RequiredIfHas());

        $validationRules = [
            'name' => 'required',
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
            $serviceDB = LocationModel::find($locationData['id']);
        } else {
            if (!Service::canCreate()) {
                throw new \WappointmentValidationException("Cannot save Modality");
            }
        }

        if (!empty($serviceDB)) {
            $serviceDB->update($locationData);
        } else {
            $serviceDB = LocationModel::create($locationData);
        }

        return $serviceDB;
    }
}
