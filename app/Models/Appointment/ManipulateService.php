<?php

namespace Wappointment\Models\Appointment;

use Wappointment\Services\AppointmentNew;

trait ManipulateService
{
    public function getServiceVideo()
    {
        return $this->getService()->getVideo();
    }

    public function getServiceName()
    {
        return  $this->getService()->name;
    }

    public function getService()
    {
        static $services = [];
        if (empty($this->service_id)) {
            return \Wappointment\Services\Service::getObject();
        }
        if (empty($services[$this->service_id])) {
            $services[$this->service_id] = \Wappointment\Services\Services::getObject($this->service_id);
        }
        return $services[$this->service_id];
    }

    public function getServiceAddress()
    {
        return AppointmentNew::getAddress($this->getService()->address, $this);
    }
}
