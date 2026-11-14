<?php
declare(strict_types=1);

namespace Wappointment\Routes;

use Wappointment\Controllers\AdminController;
use Wappointment\System\Container;
use Wappointment\Auth\AccessPolicy;

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
        $access = new AccessPolicy();
        $allowed = $access->allowedPages();
        if ($allowed === []) {
            return;
        }
        $parent = in_array('wappointment-jobs', $allowed, true)
            ? 'wappointment-jobs' : $allowed[0];
        add_menu_page(
            'Wappointment',           // Page title
            'Wappointment',           // Menu title
            $access->menuCapability($parent),
            $parent,
            $this->controller,          // Callback
            'dashicons-calendar-alt', // Icon
            20                        // Position
        );

        foreach ([
            'wappointment-appointments' => 'Appointments',
            'wappointment-booking-setup' => 'Booking setup',
            'wappointment-diagnostics' => 'Diagnostics',
            'wappointment-jobs' => 'Jobs',
            'wappointment-clients' => 'Clients',
            'wappointment-my-availability' => 'My availability',
            'wappointment-settings' => 'Settings',
        ] as $slug => $label) {
            if (!in_array($slug, $allowed, true)) {
                continue;
            }
            add_submenu_page(
                $parent, $label, $label, $access->menuCapability($slug), $slug,
                $this->controller
            );
        }
    }
}
