<?php
declare(strict_types=1);

namespace Wappointment\System;

use Wappointment\Routes\AdminMenu;

/**
 * Main plugin initialization class
 */
class Init
{
    public function __construct()
    {
        $this->registerHooks();
    }

    /**
     * Register WordPress hooks
     */
    private function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'registerAdminMenu']);
    }

    /**
     * Register admin menu pages
     */
    public function registerAdminMenu(): void
    {
        $menu = new AdminMenu();
        $menu->register();
    }
}
