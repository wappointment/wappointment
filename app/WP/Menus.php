<?php

namespace Wappointment\WP;

use Wappointment\System\Status;

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

        /**
         * TODO probably can drop that condition
         */
        if (Status::wizardComplete()) {
            $this->sub_menus['clients'] = ['label' => 'Clients', 'cap' => $this->getClientCap()];
            $this->sub_menus['settings'] = ['label' => 'Settings', 'cap' => $this->getSettingsCap()];
            $this->sub_menus['addons'] = ['label' => 'Addons', 'cap' => 'administrator'];
            $this->sub_menus['help'] = ['label' => 'Help', 'cap' => 'administrator'];
        }
        add_menu_page(
            WAPPOINTMENT_NAME,
            WAPPOINTMENT_NAME,
            $this->getCalendarCap(),
            $this->parent_slug,
            $this->load_view,
            'dashicons-wappointment',
            25
        );
    }

    public function isAdministrator()
    {
        return current_user_can('administrator');
    }

    protected function getCalendarCap()
    {
        return $this->isAdministrator() ? 'administrator' : 'wappo_calendar_man';
    }

    protected function getClientCap()
    {
        return $this->isAdministrator() ?  'administrator' : 'wappo_clients_man';
    }

    protected function getSettingsCap()
    {
        return $this->isAdministrator() ?  'administrator' : 'wappo_self_man';
    }

    public function mainCap()
    {
        return $this->isAdministrator() ?  'administrator' : 'wappo_can_use';
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
