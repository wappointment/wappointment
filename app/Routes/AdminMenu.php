<?php
declare(strict_types=1);

namespace Wappointment\Routes;

use Wappointment\Controllers\AdminController;

/**
 * Admin menu configuration
 */
class AdminMenu
{
    private AdminController $controller;

    public function __construct()
    {
        $this->controller = new AdminController();
    }

    /**
     * Register the admin menu
     */
    public function register(): void
    {
        // Add top-level menu
        add_menu_page(
            'Wappointment',           // Page title
            'Wappointment',           // Menu title
            'manage_options',         // Capability
            'wappointment-jobs',      // Menu slug
            [$this->controller, 'jobs'], // Callback
            'dashicons-calendar-alt', // Icon
            20                        // Position
        );

        add_submenu_page(
            'wappointment-jobs',
            'Jobs',
            'Jobs',
            'manage_options',
            'wappointment-jobs',
            [$this->controller, 'jobs']
        );

        add_submenu_page(
            'wappointment-jobs',
            'Clients',
            'Clients',
            'manage_options',
            'wappointment-clients',
            [$this->controller, 'clients']
        );

        add_submenu_page(
            'wappointment-jobs',
            'Settings',
            'Settings',
            'manage_options',
            'wappointment-settings',
            [$this->controller, 'settings']
        );
    }
}
