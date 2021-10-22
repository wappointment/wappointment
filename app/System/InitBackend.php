<?php

namespace Wappointment\System;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Settings;
use Wappointment\Services\Addons;
use Wappointment\Services\Reset;

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
            add_action('wp_print_scripts', [$this, 'jsVariables']);
        }

        //when a site is deleted in ms we clean the tables
        add_action('wp_uninitialize_site', [$this, 'deleteDbTables']);
    }


    public function deleteDbTables($blog_data)
    {

        switch_to_blog($blog_data->blog_id);
        $reset = new Reset;
        $reset->dropTables();
        restore_current_blog();
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
        $this->menus = new \Wappointment\WP\Menus;
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
            WAPPOINTMENT_SLUG . '_backend.back.js',
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
                'defaultEmail' => wp_get_current_user()->user_email,
                'days' => Status::installedForXDays(),
                'addons' =>  \Wappointment\Services\Addons::getActive()
            ]);

            if (Status::hasPendingUpdates()) {
                $varJs['hasPendingUpdates'] = true;
            }

            if (Status::hasMessages()) {
                $varJs['hasMessages'] = Status::hasMessages();
            }

            $varJs['canSeeUpdate'] = Status::canSeeUpdatePage();
        }

        wp_localize_script(WAPPOINTMENT_SLUG . '_backend_menu', WAPPOINTMENT_SLUG . 'Admin', $varJs);

        wp_enqueue_script(WAPPOINTMENT_SLUG . '_backend_menu');
        wp_enqueue_script(WAPPOINTMENT_SLUG . '_backend.back.js');
    }

    public function enqueueBackendAlways()
    {

        if (!empty(Addons::getActive())) {
            new \Wappointment\Services\Wappointment\VersionCheck;
        }

        add_action('current_screen', [$this, 'enqueuePlugins']);


        if (!\WappointmentLv::function_exists('register_block_type')) {
            // Gutenberg is not active.
            return;
        }
    }

    public function enqueuePlugins()
    {
        if (WPHelpers::isBackendPage('plugins')) {
            wp_register_script(
                WAPPOINTMENT_SLUG . '_feedbacks',
                Helpers::assetUrl('js/feedbacks.js'),
                ['jquery'],
                null,
                true
            );
            wp_enqueue_script(WAPPOINTMENT_SLUG . '_feedbacks');
        }
    }

    public function customPluginLinks($links)
    {
        $links[] = '<a href="' . esc_url(WPHelpers::adminUrl('wappointment_settings')) . '" >' . __('Settings', 'wappointment') . '</a>';

        if (Status::canSeeUpdatePage()) {
            /* translators: %s - version number */
            $links[] = '<a class="wappo_whatsnew" href="' . esc_url(WPHelpers::adminUrl('wappointment_calendar#see_whats_new')) . '" >' . sprintf(__('See Improvements in %s', 'wappointment'), 'v' . WAPPOINTMENT_VERSION) . '</a>';
        }

        return $links;
    }
}
