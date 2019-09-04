<?php

namespace Wappointment\System;


class Menus
{
    private $parent_slug = '';
    private $sub_menus = [];
    private $menu_capability = '';
    private $load_view = ['Wappointment\Controllers\AdminDefaultController', 'defaultContent'];

    public function __construct()
    {
        $this->parent_slug = strtolower(WAPPOINTMENT_NAME) . '_calendar';
        $this->sub_menus = [
            'calendar' => ['label' => 'Calendar'],
        ];
        if (Status::wizardComplete()) {
            $this->sub_menus['settings'] = ['label' => 'Settings'];
            $this->sub_menus['addons'] = ['label' => 'Addons'];
            $this->sub_menus['help'] = ['label' => 'Help'];
        }
        $this->menu_capability = $this->roleAllowed();

        add_menu_page(WAPPOINTMENT_NAME, WAPPOINTMENT_NAME, $this->menu_capability, $this->parent_slug, $this->load_view, 'dashicons-wappointment', 25);
    }

    public function addSubmenus()
    {
        foreach ($this->sub_menus as $sub_key => $submenu) {
            add_submenu_page($this->parent_slug, $submenu['label'], $submenu['label'], $this->menu_capability, strtolower(WAPPOINTMENT_NAME) . '_' . $sub_key, $this->load_view);
        }
    }

    private function roleAllowed()
    {
        return current_user_can('administrator') ? 'administrator' : (current_user_can('editor') ? 'editor' : 'author');
    }
}
