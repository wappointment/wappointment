<?php

namespace Wappointment\System;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Config\Database;

class Init
{
    private $is_installed = false;

    public function __construct()
    {
        $this->is_installed = $this->isInstalledAndUpdated();

        WPHelpers::requestCapture();
        Database::capsule();

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
        //new Scheduler();

        Scheduler::processQueue();
    }


    public function registeringWidget()
    {
        register_widget('Wappointment\WP\Widget');
    }

    public function jsVariables()
    {
        $variables = [
            'root' => esc_url_raw(rest_url()),
            'resourcesUrl' => Helpers::pluginUrl() . '/dist/',
            'baseUrl' => plugins_url(),
            'apiSite' => WAPPOINTMENT_SITE,
            'allowed' => \Wappointment\Services\Settings::get('wappointment_allowed')
        ];
        if (is_user_logged_in()) {
            $variables['nonce'] = wp_create_nonce('wp_rest');
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
