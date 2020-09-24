<?php

namespace Wappointment\System;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Settings;
use Wappointment\Services\Addons;

class InitBackend
{
    public $menus;

    public function __construct($isInstalledAndUpdated)
    {

        add_action('admin_init', [$this, 'enqueueMin']);
        add_action('admin_menu', [$this, 'registerMenuRoot']);
        if ($isInstalledAndUpdated) {
            add_action('admin_menu', [$this, 'registerMenuSubs']);
            add_action('admin_init', [$this, 'enqueueBackendAlways']);
            add_filter('display_post_states', [$this, 'addDisplayPostStates'], 12, 2);
            add_filter('plugin_action_links_' . plugin_basename(WAPPOINTMENT_FILE), [$this, 'customPluginLinks']);
        }
        if (WPHelpers::isPluginPage()) {
            add_action('admin_init', [$this, 'enqueueBackendPlugin']);
            add_action('admin_notices', ['\Wappointment\WP\Alerts', 'display']);
            add_action('wp_print_scripts', [$this, 'jsVariables']);
        }
    }


    public function addDisplayPostStates($post_states, $post)
    {

        if ($post->ID === Settings::get('booking_page')) {
            $post_states['wappo_booking_page'] = 'Wappointment Booking Page';
        }

        return $post_states;
    }

    public function registerMenuRoot()
    {
        $this->menus = new Menus;
    }

    public function registerMenuSubs()
    {
        $this->menus->addSubmenus();
    }

    public function jsVariables()
    {
        $variables = [];
        foreach ($this->menus->sub_menus as $key => $value) {
            $variables[] = 'wappointment_' . $key;
        }

        $return = '<script type="text/javascript">' . "\n";
        $return .= '/* Wappointment globals */ ' . "\n";
        $return .= '/* <![CDATA[ */ ' . "\n";
        $return .= 'var wappointmentBackMenus = ' . json_encode($variables) . ";\n";
        $return .= '/* ]]> */ ' . "\n";

        $return .= '</script>' . "\n";
        echo $return;
    }

    public function enqueueMin()
    {
        wp_enqueue_style(WAPPOINTMENT_SLUG . '-wap', plugins_url(WAPPOINTMENT_SLUG . '/dist/css/wappointment.css'));
    }

    public function enqueueBackendPlugin()
    {
        wp_enqueue_style(
            WAPPOINTMENT_SLUG . '-admin-wap',
            plugins_url(WAPPOINTMENT_SLUG . '/dist/css/wappointment-admin.css')
        );
        wp_register_script(
            WAPPOINTMENT_SLUG . '_backend',
            Helpers::assetUrl('main.js'),
            ['jquery'],
            null,
            true
        );

        wp_register_script(
            WAPPOINTMENT_SLUG . '_backend_menu',
            Helpers::assetUrl('js/backend_menu.js'),
            [],
            null,
            true
        );

        $varJs = ['wizardStep' => Status::wizardStep()];
        if (Status::wizardComplete()) {
            $varJs = array_merge($varJs, [
                'updatePages' => Status::newUpdates(),
                'helloIgnore' => Status::helloPage(),
                'defaultEmail' => wp_get_current_user()->user_email,
                'days' => Status::installedForXDays(),
                'addons' =>  \Wappointment\Services\Addons::getActive()
            ]);

            if (Status::hasPendingUpdates()) {
                $varJs['hasPendingUpdates'] = true;
            }

            $varJs['canSeeUpdate'] = Status::canSeeUpdatePage();
            //dd($varJs);
        }
        wp_localize_script(WAPPOINTMENT_SLUG . '_backend_menu', WAPPOINTMENT_SLUG . 'Admin', $varJs);

        wp_enqueue_script(WAPPOINTMENT_SLUG . '_backend_menu');
        wp_enqueue_script(WAPPOINTMENT_SLUG . '_backend');
    }

    public function enqueueBackendAlways()
    {

        if (!empty(Addons::getActive())) {
            new \Wappointment\Services\Wappointment\VersionCheck;
        }

        if (!\WappointmentLv::function_exists('register_block_type')) {
            // Gutenberg is not active.
            return;
        }
    }

    public function customPluginLinks($links)
    {
        $links[] = '<a href="' . esc_url(WPHelpers::adminUrl('wappointment_settings')) . '" >Settings</a>';
        if (Status::installedForXDays() > 30) {
            $links[] = '<a href="https://wordpress.org/support/plugin/wappointment/reviews/#new-post" target="_blank" class="btn btn-outline-secondary text-dark ml-2">
                Support us with stars
                <span class="dashicons dashicons-star-filled"></span> <span class="dashicons dashicons-star-filled"></span> <span class="dashicons dashicons-star-filled"></span> <span class="dashicons dashicons-star-filled"></span> <span class="dashicons dashicons-star-filled"></span>
            </a> <style>.plugins .plugin-title .dashicons.dashicons-star-filled::before {
                padding: 0px;
                background-color: transparent;
                box-shadow: none;
                font-size: 17px;
                color: #ffb900;
            }
            .plugins .plugin-title .dashicons, .plugins .plugin-title img {
                float: none;
                width: auto;
                height: auto;
                padding: 0;
            }
            </style>';
        }

        return $links;
    }
}
