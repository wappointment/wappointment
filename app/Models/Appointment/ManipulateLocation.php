<?php

namespace Wappointment\Models\Appointment;

use Wappointment\Models\Location;
use Wappointment\Services\AppointmentNew;

trait ManipulateLocation
{
    public function getLocationSlug()
    {
        switch ($this->type) {
            case self::TYPE_PHYSICAL:
                return 'physical';
            case self::TYPE_PHONE:
                return 'phone';
            case self::TYPE_ZOOM:
                return 'zoom';
        }
    }
    public function getLocation()
    {
        $location = '';
        switch ($this->type) {
            case self::TYPE_PHYSICAL:
                $location = 'Address: ' . $this->getServiceAddress();
                break;
            case self::TYPE_PHONE:
                $location = 'By Phone';
                break;
            case self::TYPE_ZOOM:
                $location = 'Video meeting';
                break;
        }
        return AppointmentNew::getLocation($location, $this);
    }

    protected function getLocationObject()
    {
        return !empty($this->location) ? $this->location : Location::find($this->location_id);
    }

    public function getLocationLabelAttribute()
    {
        return $this->getLocation();
    }

    public function getLocationVideo()
    {
        if ($this->location_id > 0) {
            $location = $this->getLocationObject();
            return !empty($location) && !empty($location->options['video']) ? $location->options['video'] : false;
        } else {
            return $this->getLocationVideoLegacy();
        }
    }

    public function getLocationVideoLegacy()
    {
        return $this->type == self::TYPE_ZOOM ? $this->getServiceVideo() : false;
    }
}
