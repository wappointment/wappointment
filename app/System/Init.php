<?php

namespace Wappointment\System;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Config\Database;
use Wappointment\Services\Settings;

class Init
{
    private $is_installed = false;

    public function __construct()
    {
        $this->is_installed = $this->isInstalledAndUpdated();

        WPHelpers::requestCapture();
        if (defined('WAPPOINTMENT_PDO_FAIL')) {
            //Database::capsule();
            // maybe we should find a way to run without pdo_mysql
        } else {
            Database::capsule();
        }


        if ($this->is_installed) {
            Listeners::init();
            add_action('init', [$this, 'baseInit']);
            add_action('init', [$this, 'initInstalled'], 30);
            add_action('widgets_init', [$this, 'registeringWidget']);
            new \Wappointment\WP\Shortcodes();
        } else {
            $this->initNotInstalled();
        }

        if (is_admin()) {
            new InitBackend($this->is_installed);
        }
    }

    private function isInstalledAndUpdated()
    {
        return (Status::isInstalled() && !Status::hasCorePendingUpdates());
    }

    public function initNotInstalled()
    {
        new InitPreinstall();
        add_action('wp_print_scripts', [$this, 'jsVariables']);
    }
    public function baseInit()
    {
        add_action('wp_print_scripts', [$this, 'jsVariables']);
        new \Wappointment\Routes\Main();
        (new \Wappointment\WP\CustomPage())->boot();
    }

    public function initInstalled()
    {
        if (\WappointmentLv::isTest() === false) {
            new Scheduler();
        } else {
            Scheduler::processQueue();
        }
    }


    public function registeringWidget()
    {
        register_widget('Wappointment\WP\Widget');
    }
    protected function getRestUrl()
    {
        if (!empty($_GET['wappo_ugly_permalinks'])) {
            Settings::save('force_ugly_permalinks', true);
        }
        return (Settings::get('force_ugly_permalinks') || empty(get_option('permalink_structure'))) ? esc_url_raw($this->getUglyRestRoute()) : esc_url_raw(rest_url());
    }

    protected function getUglyRestRoute($blog_id = null, $path = '/', $scheme = 'rest')
    {
        if (empty($path)) {
            $path = '/';
        }

        $path = '/' . ltrim($path, '/');


        $url = trailingslashit(get_home_url($blog_id, '', $scheme));
        // nginx only allows HTTP/1.0 methods when redirecting from / to /index.php.
        // To work around this, we manually add index.php to the URL, avoiding the redirect.
        if ('index.php' !== substr($url, 9)) {
            $url .= 'index.php';
        }

        $url = add_query_arg('rest_route', $path, $url);

        if (is_ssl() && isset($_SERVER['SERVER_NAME'])) {
            // If the current host is the same as the REST URL host, force the REST URL scheme to HTTPS.
            if (parse_url(get_home_url($blog_id), PHP_URL_HOST) === $_SERVER['SERVER_NAME']) {
                $url = set_url_scheme($url, 'https');
            }
        }

        if (is_admin() && force_ssl_admin()) {
            /*
                 * In this situation the home URL may be http:, and `is_ssl()` may be false,
                 * but the admin is served over https: (one way or another), so REST API usage
                 * will be blocked by browsers unless it is also served over HTTPS.
                 */
            $url = set_url_scheme($url, 'https');
        }
        return apply_filters('rest_url', $url, $path, $blog_id, $scheme);
    }

    public function jsVariables()
    {
        $variables = [
            'root' => $this->getRestUrl(),
            'resourcesUrl' => Helpers::pluginUrl() . '/dist/',
            'baseUrl' => plugins_url(),
            'apiSite' => WAPPOINTMENT_SITE,
            'allowed' => Settings::get('wappointment_allowed')
        ];
        if (is_user_logged_in()) {
            $variables['nonce'] = wp_create_nonce('wp_rest');
            $variables['wp_user'] = WPHelpers::wpUserData();
        }
        if (defined('WAPPOINTMENT_DEBUG')) {
            $variables['debug'] = true;
        }
        if (is_admin()) {
            $parsed = parse_url(WPHelpers::adminUrl('admin.php'));
            $variables['base_admin'] = !empty($parsed['path']) ? $parsed['path'] : '/wp-admin/admin.php';
        }
        $return = '<script type="text/javascript">' . "\n";
        $return .= '/* Wappointment globals */ ' . "\n";
        $return .= '/* <![CDATA[ */ ' . "\n";
        $return .= 'var apiWappointment = ' . json_encode($variables) . ";\n";
        $return .= 'var widgetWappointment = '
            . json_encode((new \Wappointment\Services\WidgetSettings)->get()) . ";\n";

        $return .= apply_filters('wappointment_js_vars', '');
        $return .= '/* ]]> */ ' . "\n";

        $return .= '</script>' . "\n";
        echo $return;
    }
}
