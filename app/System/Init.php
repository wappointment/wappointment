<?php

namespace Wappointment\System;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Config\Database;
use Wappointment\Services\Settings;
use Wappointment\Services\VersionDB;

class Init
{
    private $is_installed = false;

    public function __construct()
    {
        $this->is_installed = Status::isInstalled();

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
        //dd(\wp_get_current_user()->get_role_caps());
    }

    public function initInstalled()
    {
        if (\WappointmentLv::isTest() === false) {
            new Scheduler();
        } else {
            Scheduler::processQueue();
        }
        $this->checkSMTPValueEncryption();
    }

    /**
     * Correcting an update bug for users using the SMTP mail config
     * from 2.0.0 to 2.0.1 simply defaults to non encrypted smtp
     * TODO legacy remove once 2.1+ takes over
     *
     * @return void
     */
    public function checkSMTPValueEncryption()
    {
        if (VersionDB::equal(VersionDB::CAN_DEL_CLIENT)) {
            $mail_config = Settings::get('mail_config');
            if ($mail_config['method'] == 'smtp' && empty($mail_config['v']) && !empty($mail_config['encryption'])) {
                $mail_config['v'] = '2.0.1';
                $mail_config['encryption'] = '';
                Settings::save('mail_config', $mail_config);
            }
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
            'version' => WAPPOINTMENT_VERSION,
            'allowed' => Settings::get('wappointment_allowed'),
            'frontPage' => get_permalink((int) Settings::get('front_page')),
            'currency' => \Wappointment\Services\Currency::get(),
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
