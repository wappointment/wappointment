<?php

namespace Wappointment\System;

class Menus
{
    private $parent_slug = '';
    public $sub_menus = [];
    private $load_view = ['Wappointment\Controllers\AdminDefaultController', 'defaultContent'];

    public function __construct()
    {
        $this->parent_slug = strtolower(WAPPOINTMENT_NAME) . '_calendar';
        $this->sub_menus = [
            'calendar' => ['label' => 'Calendar', 'cap' => $this->getCalendarCap()],
        ];
        if (Status::wizardComplete()) {
            $this->sub_menus['clients'] = ['label' => 'Clients', 'cap' => $this->getClientCap()];
            $this->sub_menus['settings'] = ['label' => 'Settings', 'cap' => 'administrator'];
            $this->sub_menus['addons'] = ['label' => 'Addons', 'cap' => 'administrator'];
            $this->sub_menus['help'] = ['label' => 'Help', 'cap' => 'administrator'];
        }

        add_menu_page(
            WAPPOINTMENT_NAME,
            WAPPOINTMENT_NAME,
            $this->mainCap(),
            $this->parent_slug,
            $this->load_view,
            'dashicons-wappointment',
            25
        );
    }

    public function roleUpdated()
    {
        return false;
    }

    protected function getCalendarCap()
    {
        return $this->roleUpdated() ? 'manage_wappo_calendars' : 'administrator';
    }

    protected function getClientCap()
    {
        return true ?  'manage_wappo_clients' : 'administrator';
    }

    public function mainCap()
    {
        return true ?  'manage_wappo' : 'administrator';
    }

    public function addSubmenus()
    {
        foreach ($this->sub_menus as $sub_key => $submenu) {
            add_submenu_page(
                $this->parent_slug,
                $submenu['label'],
                $submenu['label'],
                $submenu['cap'],
                strtolower(WAPPOINTMENT_NAME) . '_' . $sub_key,
                $this->load_view
            );
        }
    }
}
