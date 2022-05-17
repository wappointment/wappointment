<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin Name: Wappointment
 * Version: 2.4.9
 * Plugin URI: https://wappointment.com
 * Description: Clients quickly book a meeting with you on Zoom , GoogleMeet , the phone or at your office
 * Author: Wappointment
 * Author URI: https://wappointment.com
 * Requires at least: 4.7
 * Requires PHP: 7.0
 * Tested up to: 5.9
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

define('WAPPOINTMENT_VERSION', '2.4.9');
define('WAPPOINTMENT_PHP_MIN', '7.0.0');
define('WAPPOINTMENT_NAME', 'Wappointment');
define('WAPPOINTMENT_SLUG', strtolower(WAPPOINTMENT_NAME));
define('WAPPOINTMENT_FILE', __FILE__);
define('WAPPOINTMENT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

require_once WAPPOINTMENT_PATH . 'app' . DIRECTORY_SEPARATOR . 'required.php';

add_action('wappointments_autoload_init', 'wappointment_starts');

function get_wappointment_autoloader()
{
    static $wappointment_loader = false;
    if ($wappointment_loader !== false) {
        return $wappointment_loader;
    }

    if (!defined('WAPPOINTMENT_PHP_FAIL')) {
        $wappointment_loader = require_once WAPPOINTMENT_PATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
        do_action('wappointments_autoload_init');
    }
}

get_wappointment_autoloader();

function wappointment_starts()
{
    new \Wappointment\System\Init();
}
