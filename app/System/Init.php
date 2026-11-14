<?php
declare(strict_types=1);

namespace Wappointment\System;

use Wappointment\Routes\AdminMenu;
use Wappointment\Routes\RestApi;
use Wappointment\Database\WpDbConnector;
use Wappointment\Database\BookingSchema;

/**
 * Main plugin initialization class
 */
class Init
{
    public function __construct()
    {
        LegacyAddons::block();
        $this->bootContainer();
        $this->registerHooks();
    }

    /**
     * Bootstrap dependency injection container
     */
    private function bootContainer(): void
    {
        $container = Container::getInstance();
        
        // Register WpDbConnector as singleton
        $container->singleton(WpDbConnector::class, function() {
            return new WpDbConnector();
        });
        
        // Register Settings as singleton
        $container->singleton(Settings::class, function() {
            return new Settings();
        });
    }

    /**
     * Register WordPress hooks
     */
    private function registerHooks(): void
    {
        add_action('init', static function (): void {
            if (get_option('wappointment_booking_schema') !== '3.0.0') {
                try {
                    BookingSchema::install();
                } catch (\RuntimeException $error) {
                    error_log('Wappointment booking setup failed: ' . $error->getMessage());
                    add_action('admin_notices', static function (): void {
                        if (current_user_can('manage_options')) {
                            echo '<div class="notice notice-error"><p>' . esc_html__(
                                'Wappointment could not initialize its booking tables. Check the database permissions and server error log before accepting bookings.',
                                'wappointment'
                            ) . '</p></div>';
                        }
                    });
                }
            }
        }, 1);
        add_action('admin_notices', static function (): void {
            if (current_user_can('manage_options')) {
                echo '<div class="notice notice-warning"><p>' . esc_html__(
                    'Wappointment v3 is under development. Free booking parity and data migration are not complete. Test on a staging copy, not a production booking site.',
                    'wappointment'
                ) . '</p></div>';
            }
        });
        add_action('admin_menu', [$this, 'registerAdminMenu']);
        add_action('rest_api_init', [$this, 'registerRestApi']);
        add_action('init', [$this, 'registerShortcodes']);
        add_action('admin_init', [$this, 'generateRoutes']);
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
        $container = Container::getInstance();
        $api = $container->make(RestApi::class);
        $api->register();
    }

    /**
     * Register shortcodes
     */
    public function registerShortcodes(): void
    {
        $shortcodes = new Shortcodes();
        $shortcodes->register();
    }

    /**
     * Generate JavaScript routes file
     */
    public function generateRoutes(): void
    {
        // Only generate in development or when routes change
        $routesFile = WAPPOINTMENT_PATH . 'resources/js-react/config/routes.js';
        $apiFile = WAPPOINTMENT_PATH . 'app/Routes/api.php';
        
        if (!file_exists($routesFile) || filemtime($apiFile) > filemtime($routesFile)) {
            $generator = new RoutesGenerator();
            $generator->generate();
        }
    }
}
