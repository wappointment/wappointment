<?php

namespace Wappointment\Services;

use Wappointment\Models\Calendar;

class Permissions
{
    private $capabilities = [];
    private $roles = [];

    public function __construct()
    {
        $this->setCapabilities();
        $this->setRoles();
    }

    public static function getAllWpRoles()
    {
        $array_roles = [];
        foreach (wp_roles()->roles as $keyRole => $role) {
            $array_roles[] = [
                'key' => $keyRole,
                'name' => $role['name']
            ];
        }
        return $array_roles;
    }

    public static function hasManagerRole()
    {
        $roles = static::getAllWpRoles();
        foreach ($roles as $role) {
            if ($role['key'] == 'wappointment_manager') {
                return true;
            }
        }
        return false;
    }

    public function registerRole($roleKey)
    {
        if (!empty($this->roles[$roleKey])) {
            $wp_roles = $this->loadWPRoles();
            add_role($roleKey, $this->roles[$roleKey]['name'], $this->roles[$roleKey]['caps']);

            if ($roleKey == 'wappointment_staff') {
                foreach ($this->getCaps(true) as $cap) {
                    $wp_roles->add_cap($roleKey, $cap);
                }
            }
        }
    }

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

    protected function loadWPRoles()
    {
        global $wp_roles;

        if (!class_exists('\WP_Roles')) {
            throw new \WappointmentException("Cannot initialize WP roles", 1);
        }

        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles;
        }
        return $wp_roles;
    }

    protected function setRoles()
    {
        $this->roles = [
            'wappointment_staff' => [
                'name' => __('Wappointment Staff', 'wappointment'),
                'caps' => [
                    'read' => true, // allows access to admin screens
                    'edit_posts' => true // allows access to admin screens when woocommerce is active
                ]
            ],
            'wappointment_manager' => [
                'name' => __('Wappointment manager', 'wappointment'),
                'caps' => [
                    'read' => true, // allows access to admin screens
                    'edit_posts' => true // allows access to admin screens when woocommerce is active
                ]
            ]
        ];
    }
    protected function setCapabilities()
    {
        $this->capabilities = $this->capabilitiesStaff();
    }

    protected function capabilitiesStaff()
    {
        return [
            'wappo_calendar_man' => [
                'name' => __('Can manage own calendar', 'wappointment'),
                'sub_caps' => [
                    'wappo_calendar_add_busy' => __('Can add Busy blocks', 'wappointment'),
                    'wappo_calendar_del_busy' => __('Can delete Busy blocks', 'wappointment'),
                    'wappo_calendar_add_free' => __('Can add Free blocks', 'wappointment'),
                    'wappo_calendar_del_free' => __('Can delete Free blocks', 'wappointment'),
                    'wappo_calendar_reschedule' => __('Can reschedule appointment', 'wappointment'),
                    'wappo_calendar_cancel' => __('Can cancel appointment', 'wappointment'),
                    'wappo_calendar_book' => __('Can book on behalf of a client', 'wappointment'),
                    'wappo_calendar_confirm' => __('Can confirm pending appointment', 'wappointment'),
                ]
            ],
            'wappo_self_man' => [
                'name' => __('Can manage own settings', 'wappointment'),
                'sub_caps' => [
                    'wappo_self_weekly' => __('Can modify weekly availability', 'wappointment'),
                    'wappo_self_services' => __('Can modify services provided', 'wappointment'),
                    'wappo_self_connect_account' => __('Can connect Wappointment.com account', 'wappointment'),
                    'wappo_self_add_ics' => __('Can add ICS calendar', 'wappointment'),
                    'wappo_self_del_ics' => __('Can delete ICS calendar', 'wappointment'),
                    'wappo_self_unpublish' => __('Can publish/unpublish self', 'wappointment'),
                    'wappo_self_cf' => __('Can edit custom fields', 'wappointment'),
                    'wappo_self_shortcode' => __('Can show shortcode', 'wappointment'),
                ]
            ],
            'wappo_clients_man' => [
                'name' => __('Can manage own clients', 'wappointment'),
                'sub_caps' => [
                    'wappo_clients_del' => __('Can delete clients', 'wappointment'),
                    'wappo_clients_edit' => __('Can edit clients', 'wappointment'),
                ]
            ],
        ];
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

    public function refreshStaffCap()
    {
        $wappo_staff_role = get_role('wappointment_staff');
        foreach ($this->getCaps(true) as $cap) {
            if (!$wappo_staff_role->has_cap($cap)) {
                $wappo_staff_role->add_cap($cap);
            }
        }
    }
}
