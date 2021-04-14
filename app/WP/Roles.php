<?php

namespace Wappointment\WP;

class Roles
{
    public $roles = [
        'wappointment_staff' => [
            'name' => 'Wappointment Staff',
            'base_caps' => [
                'read' => true,
            ]
        ]
    ];
    public $capabilities = [
        'manage_wappo_calendar'
    ];

    public function install()
    {
        foreach ($this->roles as $role_key => $data) {
            add_role(
                $role_key,
                $data['name'],
                $data['base_caps']
            );
        }
    }

    public function addCapabilities()
    {
        $wp_roles = $this->getRolesObject();
        if (empty($wp_roles)) {
            throw new \Exception("Cannot load roles", 1);
        }

        foreach ($this->capabilities as $cap) {
            $wp_roles->add_cap('wappointment_staff', $cap);
            $wp_roles->add_cap('administrator', $cap);
        }
    }

    public function getRolesObject()
    {
        global $wp_roles;

        if (!class_exists('WP_Roles')) {
            return null;
        }

        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }
        return $wp_roles;
    }
}
