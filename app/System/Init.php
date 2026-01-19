<?php
declare(strict_types=1);

namespace Wappointment\System;

use Wappointment\Routes\AdminMenu;
use Wappointment\Routes\RestApi;

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
        add_action('rest_api_init', [$this, 'registerRestApi']);
    }

    /**
     * Register admin menu pages
     */
    public function registerAdminMenu(): void
    {
        $menu = new AdminMenu();
        $menu->register();
    }

    /**
     * Register REST API routes
     */
    public function registerRestApi(): void
    {
        $api = new RestApi();
        $api->register();
    }
}
