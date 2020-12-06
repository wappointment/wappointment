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
        $this->service = $serviceArray;
        $this->name = $serviceArray['name'];
        $this->type = !empty($serviceArray['type']) && is_array($serviceArray['type']) ? $serviceArray['type'] : [];
        $this->address = $this->hasPhysical() && !empty($serviceArray['address']) ? $serviceArray['address'] : '';
        $this->duration = empty($serviceArray['duration']) ? 0 : $serviceArray['duration'];
        $this->options = empty($serviceArray['options']) ? [] : $serviceArray['options'];
    }

    public function getDurations()
    {
        return [$this->duration];
    }

    public function getTypes()
    {
        return $this->type;
    }

    public function getVideo()
    {
        return !empty($this->options['video']) ? $this->options['video'] : false;
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
    public function hasZoom()
    {
        return $this->hasType('zoom');
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
