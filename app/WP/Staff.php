<?php

namespace Wappointment\WP;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Service;

class Staff
{
    public $wp_user = null;
    public $id = null;
    public $avatar = null; //avatar
    public $name = null; //staffname
    public $timezone = null; //staff timezone
    public $gravatar = '';
    public $availability = '';
    public $staff_data = [];

    public function __construct($staff_data = [])
    {
        if (empty($staff_data)) {
            throw new \WappointmentException("Can't load staff information", 1);
        }
        $this->staff_data = $staff_data;

        $this->id = (int)$this->staff_data['wp_uid'];
        $this->wp_user = get_userdata($this->id);
        unset($this->wp_user->data->user_pass);
        unset($this->wp_user->data->user_activation_key);
        if (empty($this->wp_user)) {
            throw new \WappointmentException("Can't load staff information", 1);
        }

        $this->gravatar = get_avatar_url($this->id, ['size' => 46]);
        $this->avatar = $this->staff_data['options']['avatar_id'] ? wp_get_attachment_image_src($this->staff_data['options']['avatar_id'])[0] : $this->gravatar;
        $this->name = $staff_data['name'];
        $this->timezone = $this->staff_data['options']['timezone'];
        $this->availability = $this->staff_data['availability'];
    }

    public function fullData()
    {
        return [
            'id' => $this->id,
            'avatar' => $this->avatar,
            'gravatar' => $this->gravatar,
            'name' => $this->name,
            'avb' => $this->getAvb(),
            'regav' => $this->getRegav(),
            'timezone' => $this->timezone,
            'services' => [Service::get()],
            'connected' => $this->getDotcom(),
            'calendar_urls' => $this->getCalendarUrls(),
            'calendar_logs' => $this->getCalendarLogs(),
        ];
    }


    public function getAvailability()
    {
        return $this->staff_data['availability'];
    }

    public function toArray()
    {
        return apply_filters('wappointment_staff_data', [
            'id' => $this->id,
            'a' => $this->avatar,
            'n' => $this->name,
            't' => $this->timezone,
            'availability' => $this->getAvailability(),
        ], $this);
    }

    public function getRegav()
    {
        return $this->staff_data['options']['regav'];
    }

    public function getAvb()
    {
        return $this->staff_data['options']['avb'];
    }

    public function getDotcom()
    {
        return $this->staff_data['options']['dotcom'];
    }

    public function getCalendarUrls()
    {
        return $this->staff_data['options']['calurl'];
    }

    public function getCalendarLogs()
    {
        return WPHelpers::getStaffOption('calendar_logs', $this->id);
    }

    public function emailAddress()
    {
        return $this->wp_user->data->user_email;
    }
}
