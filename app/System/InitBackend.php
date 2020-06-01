<?php

namespace Wappointment\System;

use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Services\Settings;

class InitBackend
{
    public $menus;

    public function __construct($isInstalledAndUpdated)
    {

        $addons_activated = true; //TODO get the list of activated addons if there are
        if ($addons_activated) {
            new \Wappointment\Services\Wappointment\VersionCheck;
        }
        add_action('admin_init', [$this, 'enqueueMin']);
        add_action('admin_menu', [$this, 'registerMenuRoot']);
        if ($isInstalledAndUpdated) {
            add_action('admin_menu', [$this, 'registerMenuSubs']);
            add_action('admin_init', [$this, 'enqueueBackendAlways']);
            add_filter('display_post_states', [$this, 'addDisplayPostStates'], 12, 2);
        }
        if (WPHelpers::isPluginPage()) {
            add_action('admin_init', [$this, 'enqueueBackendPlugin']);
            add_action('admin_notices', ['\Wappointment\WP\Alerts', 'display']);
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
        $this->menus = new Menus();
    }

    public function registerMenuSubs()
    {
        $this->menus->addSubmenus();
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
        }
        wp_localize_script(WAPPOINTMENT_SLUG . '_backend_menu', WAPPOINTMENT_SLUG . 'Admin', $varJs);

        wp_enqueue_script(WAPPOINTMENT_SLUG . '_backend_menu');
        wp_enqueue_script(WAPPOINTMENT_SLUG . '_backend');
    }

    public function enqueueBackendAlways()
    {

        if (!\WappointmentLv::function_exists('register_block_type')) {
            // Gutenberg is not active.
            return;
        }
    }
}
