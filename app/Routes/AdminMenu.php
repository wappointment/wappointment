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
            'wappointment',           // Menu slug
            [$this->controller, 'page1'], // Callback
            'dashicons-calendar-alt', // Icon
            20                        // Position
        );

        // Add submenu pages
        add_submenu_page(
            'wappointment',           // Parent slug
            'Page 1',                 // Page title
            'Page 1',                 // Menu title
            'manage_options',         // Capability
            'wappointment',           // Menu slug (same as parent for first item)
            [$this->controller, 'page1'] // Callback
        );

        add_submenu_page(
            'wappointment',
            'Page 2',
            'Page 2',
            'manage_options',
            'wappointment-page2',
            [$this->controller, 'page2']
        );

        add_submenu_page(
            'wappointment',
            'Page 3',
            'Page 3',
            'manage_options',
            'wappointment-page3',
            [$this->controller, 'page3']
        );

        add_submenu_page(
            'wappointment',
            'Jobs',
            'Jobs',
            'manage_options',
            'wappointment-jobs',
            [$this->controller, 'jobs']
        );

        add_submenu_page(
            'wappointment',
            'Clients',
            'Clients',
            'manage_options',
            'wappointment-clients',
            [$this->controller, 'clients']
        );
    }
}
