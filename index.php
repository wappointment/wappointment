<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin Name: Wappointment
 * Version: 3.0.0
 * Plugin URI: https://wappointment.com
 * Description: Clients quickly book a meeting with you on Zoom , GoogleMeet , the phone or at your office
 * Author: Wappointment
 * Author URI: https://wappointment.com
 * Requires at least: 6.0
 * Requires PHP: 8.2
 * Tested up to: 6.9
 *
 * Text Domain: wappointment
 * Domain Path: /languages
 * @package Wappointment
 * @author Wappointment
 * @since 1.0.0
 *
 * Wappointment is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Wappointment is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

define('WAPPOINTMENT_VERSION', '3.0.0');
define('WAPPOINTMENT_PHP_MIN', '8.2.0');
define('WAPPOINTMENT_NAME', 'Wappointment');
define('WAPPOINTMENT_SLUG', strtolower(WAPPOINTMENT_NAME));
define('WAPPOINTMENT_FILE', __FILE__);
define('WAPPOINTMENT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

// Check PHP version before loading
if (version_compare(PHP_VERSION, WAPPOINTMENT_PHP_MIN, '<')) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>';
        echo sprintf(
            'Wappointment requires PHP %s or higher. You are running PHP %s.',
            WAPPOINTMENT_PHP_MIN,
            PHP_VERSION
        );
        echo '</p></div>';
    });
    return;
}

// Simple PSR-4 autoloader for Wappointment namespace
spl_autoload_register(function ($class) {
    $prefix = 'Wappointment\\';
    $base_dir = WAPPOINTMENT_PATH . 'app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load helper functions
require_once WAPPOINTMENT_PATH . 'app/helpers.php';

// Initialize plugin
add_action('plugins_loaded', function () {
    new \Wappointment\System\Init();
});

