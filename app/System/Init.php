<?php

namespace Wappointment\System;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Config\Database;
use Wappointment\Services\Settings;

class Init
{
    private $is_installed = false;
    private $runs_php8 = false;

    public function __construct()
    {
        $this->runs_php8 = Status::runningPHP8();
        $this->is_installed =  Status::isInstalled();
        WPHelpers::requestCapture($this->is_installed);
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
        if (!\Wappointment\Services\Payment::isWooActive()) {
            add_filter('wappointment_package_save', ['\\Wappointment\\Services\\AdminPackage', 'dataSave'], 10, 2);
            add_filter('wappointment_package_delete', ['\\Wappointment\\Services\\AdminPackage', 'delete']);
        }

        add_action('wappointment_cancel_ticket', ['\\Wappointment\\Services\\Ticket', 'cancel'], 10, 1);
    }

    public function initInstalled()
    {
        if (Helpers::isProd()) {
            new Scheduler;
        } else {
            Scheduler::processQueue();
        }
    }

    public function registeringWidget()
    {
        register_widget('Wappointment\WP\Widget');
    }

    public function jsVariables()
    {
        $variables = [
            'root' => Compatibility::getRestUrl(),
            'resourcesUrl' => Helpers::pluginUrl() . '/dist/',
            'baseUrl' => plugins_url(),
            'apiSite' => WAPPOINTMENT_SITE,
            'version' => WAPPOINTMENT_VERSION,
            'allowed' => Settings::get('wappointment_allowed'),
            'frontPage' => get_permalink((int) Settings::get('front_page')),
            'currency' => \Wappointment\Services\Payment::currency(),
            'methods' => \Wappointment\Services\Payment::methods(),
            'signature' => \Wappointment\Services\IcsGenerator::getIcsSignature(),
        ];

        if (is_user_logged_in()) {
            $variables['nonce'] = wp_create_nonce('wp_rest');
        }
        if (defined('WP_DEBUG')) {
            $variables['debug'] = true;
        }
        if ($this->runs_php8) {
            $variables['error_php8'] = $this->runs_php8;
        }
        if (is_admin()) {
            $parsed = parse_url(WPHelpers::adminUrl('admin.php'));
            $variables['base_admin'] = !empty($parsed['path']) ? $parsed['path'] : '/wp-admin/admin.php';
            $variables['wp_user'] = WPHelpers::wpUserData(true);
        }
        $return = '<script type="text/javascript">' . "\n";
        $return .= '/* Wappointment globals */ ' . "\n";
        $return .= '/* <![CDATA[ */ ' . "\n";
        $return .= 'var apiWappointment = ' . json_encode($variables) . ";\n";
        $return .= 'var widgetWappointment = '
            . json_encode((new \Wappointment\Services\WidgetSettings)->get()) . ";\n";

        if (is_admin()) {
            $return .= 'var wappoEmailTags =' .  $this->getWappoEmailTags() . ";\n";
            $return .= 'var wappoEmailLinks =' .  $this->getWappoEmailLinks() . ";\n";
        }
        $return .= apply_filters('wappointment_js_vars', '');
        $return .= '/* ]]> */ ' . "\n";

        $return .= '</script>' . "\n";
        echo $return;
    }

    public function getWappoEmailLinks()
    {
        return json_encode(\Wappointment\Messages\TagsReplacement::emailsLinks());
    }

    public function getWappoEmailTags()
    {
        return json_encode(\Wappointment\Messages\TagsReplacement::emailsTags());
    }
}
