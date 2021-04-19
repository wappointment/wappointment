<?php

namespace Wappointment\WP;

use Wappointment\Models\Calendar;

class Staff
{
    public $wp_user = null;
    public $id = null;
    public $wp_uid = null;
    public $avatar = null; //avatar
    public $avatar_id = null; //avatar_id
    public $name = null; //staffname
    public $timezone = null; //staff timezone
    public $gravatar = '';
    public $status = false;
    public $availability = '';
    public $staff_data = [];
    public $permissions = [];

    public function __construct($staff_data = [])
    {
        if (empty($staff_data)) {
            throw new \WappointmentException("Can't load staff information", 1);
        }

        if (!is_array($staff_data)) {
            $calendar = Calendar::findOrFail($staff_data);
            $staff_data = $calendar->toArray();
        }
        $this->initWithArray($staff_data);
    }

    protected function initWithArray($staff_data)
    {
        $this->staff_data = $staff_data;

        $this->id = (int)$this->staff_data['id'];
        $this->wp_uid = (int)$this->staff_data['wp_uid'];
        $this->status = (int)$this->staff_data['status'];
        $this->wp_user = get_userdata($this->wp_uid);
        unset($this->wp_user->data->user_pass);
        unset($this->wp_user->data->user_activation_key);
        if (empty($this->wp_user) && $this->status > 0) {
            Calendar::where('id', $this->id)->update(['status' => 0]);
        }

        $this->gravatar = get_avatar_url($this->wp_uid, ['size' => 46]);
        $this->avatar_id = !empty($this->staff_data['options']['avatar_id']) ? $this->staff_data['options']['avatar_id'] : '';
        $this->avatar = !empty($this->avatar_id) ? wp_get_attachment_image_src($this->avatar_id)[0] : $this->gravatar;
        $this->name = $staff_data['name'];
        $this->timezone = $this->staff_data['options']['timezone'];
        $this->availability = $this->staff_data['availability'];
        $this->setWappoPermissions();
    }

    protected function setWappoPermissions()
    {
        $permissions = new \Wappointment\Services\Permissions;

        $this->permissions = $permissions->getUserCaps($this->wp_user->get_role_caps());
    }

    public function fullData()
    {

        return [
            'id' => $this->id,
            'wp_uid' => $this->wp_uid,
            'avatar' => $this->avatar,
            'gravatar' => $this->gravatar,
            'avatar_id' => $this->avatar_id,
            'name' => $this->name,
            'avb' => $this->getAvb(),
            'regav' => $this->getRegav(),
            'timezone' => $this->timezone,
            'services' => $this->getServicesId($this->staff_data['services']),
            'connected' => $this->getDotcom(),
            'status' => $this->status,
            'calendar_urls' => $this->getCalendarUrls(),
            'calendar_logs' => $this->getCalendarLogs(),
            'permissions' => $this->permissions,
            'role' => $this->wp_user->get_role()
        ];
    }

    public function getServicesId($services)
    {
        $service_ids = [];
        foreach ($services as $service) {
            $service_ids[] = $service['id'];
        }
        return $service_ids;
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
        return $this->staff_data['availability'];
    }

    public function toArray()
    {
        return apply_filters('wappointment_staff_data', [
            'id' => $this->id,
            'a' => $this->avatar,
            'n' => $this->name,
            't' => $this->timezone,
            'services' => $this->getServicesId($this->staff_data['services']),
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
        return !empty($this->staff_data['options']['dotcom']) ? $this->staff_data['options']['dotcom'] : false;
    }

    public function getCalendarUrls()
    {
        return !empty($this->staff_data['options']['cal_urls']) ? $this->staff_data['options']['cal_urls'] : [];
    }

    public function getCalendarLogs()
    {
        return !empty($this->staff_data['options']['calendar_logs']) ? $this->staff_data['options']['calendar_logs'] : [];
    }

    public function emailAddress()
    {
        return $this->wp_user->data->user_email;
    }
}
