<?php

namespace Wappointment\Controllers;

use Wappointment\Services\Service;
use Wappointment\ClassConnect\Request;
use Wappointment\Models\Location;
use Wappointment\Services\VersionDB;
use Wappointment\Services\Services;

class ServiceController extends RestController
{
    public function save(Request $request)
    {
        if (VersionDB::canServices()) {
            $locationsIds = $this->updateLocations($request);
            $optionsTemp = $request->input('options');

            $options = [
                'durations' => [['duration' => $request->input('duration')]],
            ];
            if (!empty($optionsTemp['phone_required'])) {
                $options['fields'] = ['name', 'email', 'phone'];
                $options['countries'] = $optionsTemp['countries'];
            }
            $dataService = [
                'name' => $request->input('name'),
                'options' => $options,
                'locations_id' => $locationsIds->toArray()
            ];
            if (!empty($request->input('id'))) {
                $dataService['id'] = $request->input('id');
            }
            Services::save($dataService);

            return true;
        } else {
            return $this->isTrueOrFail(Service::save($request->only(['name', 'duration', 'type', 'address', 'options'])));
        }
    }

    protected function updateLocations(Request $request)
    {
        $options = $request->input('options');
        $typeId = [];
        foreach ($request->input('type') as $type_name) {
            $typeId[] = $this->getLocationTypeId($type_name);
        }

        $locations = Location::whereIn('type', $typeId)->get();
        foreach ($locations as $location) {
            $optionsTemp = $location->options;
            if ($location->type == Location::TYPE_ZOOM) {
                $optionsTemp['video'] = $options['video'];
            }
            if ($location->type == Location::TYPE_AT_LOCATION) {
                $optionsTemp['address'] = $request->input('address');
            }
            if ($location->type == Location::TYPE_PHONE) {
                $optionsTemp['countries'] = $options['countries'];
            }
            $location->options = $optionsTemp;
            $location->save();
        }
        return $locations->map(function ($locationObj) {
            return $locationObj->id;
        });
    }
    protected function getLocationTypeId($type_name)
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
