<?php

namespace Wappointment\Models\Appointment;

use Wappointment\Services\Settings;
use Wappointment\Services\VersionDB;

trait ManipulateStaff
{
    public function getStaff()
    {
        if (VersionDB::isLessThan(VersionDB::CAN_CREATE_SERVICES)) {
            return \Wappointment\Services\Staff::getById($this->staff_id);
        } else {
            return new \Wappointment\WP\Staff((int)$this->staff_id);
        }
    }

    public function getStaffCustomField($tagInfo = false)
    {
        return !empty($this->getStaff()->staff_data['options']['custom_fields'][$tagInfo['key']]) ? $this->getStaff()->staff_data['options']['custom_fields'][$tagInfo['key']] : '';
    }

    public function getStaffName()
    {
        return $this->getStaff()->staff_data['name'];
    }

    public function getStaffTZ()
    {
        return $this->getStaff()->timezone;
    }

    public function getStaffId()
    {
        return VersionDB::canServices() ? $this->staff_id : Settings::get('activeStaffId');
    }
}
