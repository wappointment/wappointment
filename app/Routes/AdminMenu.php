<?php
declare(strict_types=1);

namespace Wappointment\Routes;

use Wappointment\Controllers\AdminController;
use Wappointment\System\Container;

/**
 * Admin menu configuration
 */
class AdminMenu
{
    private AdminController $controller;

    public function __construct()
    {
        $this->controller = Container::getInstance()->make(AdminController::class);
    }

    /**
     * Register the admin menu
     */
    public function register(): void
    {
        foreach ([
            'wappointment-appointments' => 'Appointments',
            'wappointment-booking-setup' => 'Booking setup',
        ] as $slug => $label) {
            add_submenu_page(
                'wappointment-jobs', $label, $label, 'manage_options', $slug,
                $this->controller
            );
        }
        // Add top-level menu
        add_menu_page(
            'Wappointment',           // Page title
            'Wappointment',           // Menu title
            'manage_options',         // Capability
            'wappointment-jobs',      // Menu slug
            $this->controller,          // Callback
            'dashicons-calendar-alt', // Icon
            20                        // Position
        );

        add_submenu_page(
            'wappointment-jobs',
            'Jobs',
            'Jobs',
            'manage_options',
            'wappointment-jobs',
            $this->controller
        );

        add_submenu_page(
            'wappointment-jobs',
            'Clients',
            'Clients',
            'manage_options',
            'wappointment-clients',
            $this->controller
        );

        add_submenu_page(
            'wappointment-jobs',
            'Settings',
            'Settings',
            'manage_options',
            'wappointment-settings',
            $this->controller
        );
    }
}
