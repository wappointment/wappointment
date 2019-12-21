<?php

namespace Wappointment\Decorators;

class Service
{
    public $service = [];

    public $name = '';
    public $type = [];
    public $address = '';
    public $duration = '';
    public $options = [];

    public function __construct($serviceArray)
    {
        /* if (
            empty($serviceArray['name'])
            || (empty($serviceArray['type']) || (!is_array($serviceArray['type'])))
            || empty($serviceArray['duration'])
        ) {
            throw new \WappointmentException("Error with the service instance");
        } */
        $this->service = $serviceArray;
        $this->name = $serviceArray['name'];
        $this->type = is_array($serviceArray['type']) ? $serviceArray['type'] : [];
        $this->address = $this->hasPhysical() && !empty($serviceArray['address']) ? $serviceArray['address'] : '';
        $this->duration = $serviceArray['duration'];
        $this->options = $serviceArray['options'];
    }

    public function hasManyTypes()
    {
        return count($this->type) > 1;
    }
    public function hasPhone()
    {
        return $this->hasType('phone');
    }
    public function hasSkype()
    {
        return $this->hasType('skype');
    }
    public function hasPhysical()
    {
        return $this->hasType('physical');
    }
    public function hasType($type)
    {
        return in_array($type, $this->type);
    }
    public function getCountries()
    {
        return empty($this->options['countries']) ? [] : $this->options['countries'];
    }
}
