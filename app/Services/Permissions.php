<?php

namespace Wappointment\Services;

use Wappointment\Models\Calendar;

class Permissions
{
    private $capabilities = [
        'wappo_self_man' => [
            'name' => 'Can manage own settings',
            'sub_caps' => [
                'wappo_self_weekly' => 'Can set own weekly availability',
                'wappo_self_services' => 'Can select services provided',
            ]
        ],
        'wappo_clients_man' => [
            'name' => 'Can manage clients',
            'sub_caps' => [
                'wappo_clients_del' => 'Can delete clients',
                'wappo_clients_edit' => 'Can edit clients',
            ]
        ]

    ];

    public function getCaps($flat = false)
    {
        return $flat ? $this->flattenCaps() : $this->capabilities;
    }

    public function getUserCaps(array $arrayUserCaps)
    {
        $caps = [];
        foreach ($arrayUserCaps as $key => $value) {
            if ($this->isWappoPerm($key)) {
                $caps[] = $key;
            }
        }
        return $caps;
    }

    protected function flattenCaps()
    {
        $flat_caps = [];
        foreach ($this->capabilities as $cap_key => $cap_object) {
            $flat_caps[] = $cap_key;
            if (!empty($cap_object['sub_caps'])) {
                foreach ($cap_object['sub_caps'] as $subkey => $value) {
                    $flat_caps[] = $subkey;
                }
            }
        }
        return $flat_caps;
    }

    protected function isWappoPerm($perm)
    {
        return strpos($perm, 'wappo_') !== false;
    }

    public function assign(Calendar $calendar, array $permissionsSaving)
    {
        if (!$calendar->isStaff()) {
            throw new \WappointmentException("Cannot assign permission to calendar", 1);
        }
        $wp_user = get_userdata($calendar->wp_uid);
        foreach ($this->getCaps() as $cap_key => $cap_object) {
            $this->toggleCap($wp_user, $cap_key, $permissionsSaving, $cap_object);
        }
    }

    private function toggleCap($wp_user, $cap_key, $permissionsSaving, $cap_object = false)
    {

        if (in_array($cap_key, $permissionsSaving)) {
            $wp_user->add_cap($cap_key);
            if ($cap_object && !empty($cap_object['sub_caps'])) {
                foreach ($cap_object['sub_caps'] as $sub_cap => $cap_label) {
                    $this->toggleCap($wp_user, $sub_cap, $permissionsSaving);
                }
            }
        } else {
            $this->removeCaps($wp_user, $cap_key, $cap_object);
        }
    }

    protected function removeCaps($wp_user, $cap_key, $cap_object)
    {
        $this->removeSingleCap($wp_user, $cap_key);
        $this->removeSubCaps($wp_user, $cap_object);
    }
    protected function removeSingleCap($wp_user, $cap_key)
    {
        if ($wp_user->has_cap($cap_key)) {
            $wp_user->remove_cap($cap_key);
        }
    }

    protected function removeSubCaps($wp_user, $cap_object)
    {
        if (!empty($cap_object['sub_caps'])) {
            foreach ($cap_object['sub_caps'] as $sub_cap_key => $sub_cap) {
                $this->removeSingleCap($wp_user, $sub_cap_key);
            }
        }
    }
}
