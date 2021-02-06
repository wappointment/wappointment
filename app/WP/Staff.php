<?php

namespace Wappointment\WP;

use Wappointment\Services\Settings;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Service;

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
        unset($this->wp_user->data->user_pass);
        unset($this->wp_user->data->user_activation_key);
        if (empty($this->wp_user)) {
            throw new \WappointmentException("Can't load staff information", 1);
        }
        $this->id = $staff_id;
        $this->gravatar = get_avatar_url(Settings::get('activeStaffId'), ['size' => 46]);
        $this->avatar = Settings::getStaff('avatarId') ? wp_get_attachment_image_src(Settings::getStaff('avatarId'))[0] : $this->gravatar;
        $dname = Settings::getStaff('display_name');
        $this->name = !empty($dname) ? $dname : $this->getUserDisplayName();
        $this->timezone = Settings::getStaff('timezone', $staff_id);
    }

    public function fullData()
    {
        return [
            'id' => $this->id,
            'avatar' => $this->avatar,
            'gravatar' => $this->gravatar,
            'name' => $this->name,
            'avb' => Settings::getStaff('availaible_booking_days'),
            'regav' => Settings::getStaff('regav'),
            'timezone' => $this->timezone,
            'services' => [Service::get()],
            'connected' => Settings::getStaff('dotcom'),
        ];
    }

    public function getUserDisplayName()
    {
        return empty($this->wp_user->display_name) ?
            $this->wp_user->first_name . ' ' . $this->wp_user->last_name : $this->wp_user->display_name;
    }

    public function getFirstName()
    {
        return $this->wp_user->first_name;
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
