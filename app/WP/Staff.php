<?php

namespace Wappointment\WP;

use Wappointment\Services\Settings;
use Wappointment\WP\Helpers as WPHelpers;

class Staff
{
    public $wp_user = null;
    public $id = null;
    public $avatar = null; //avatar
    public $name = null; //staffname
    public $timezone = null; //staff timezon

    public function __construct($staff_id = false)
    {
        if ($staff_id === false) {
            $staff_id = Settings::get('activeStaffId');
        }
        $this->wp_user = get_userdata($staff_id);
        if (empty($this->wp_user)) {
            throw new \WappointmentException("Can't load staff information", 1);
        }
        $this->id = $staff_id;
        $this->avatar = get_avatar_url($staff_id, ['size' => 40]);
        $this->name = $this->getUserDisplayName();
        $this->timezone = Settings::getStaff('timezone', $staff_id);
    }

    public function getUserDisplayName()
    {
        return empty($this->wp_user->display_name) ? $this->wp_user->first_name . ' ' . $this->wp_user->last_name : $this->wp_user->display_name;
    }

    public function getAvailability()
    {
        if ($this->id > 0) {
            return WPHelpers::getStaffOption('availability', $this->id);
        }
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'a' => $this->avatar,
            'n' => $this->name,
            't' => $this->timezone,
        ];
    }

    public function emailAddress()
    {
        return $this->wp_user->data->user_email;
    }
}
