<?php

namespace Wappointment\System;

use Wappointment\WP\Helpers as WPHelpers;

class InitBackend
{
    public $menus;

    public function __construct($isInstalledAndUpdated)
    {
        $addons_activated = true; //TODO get the list of activated addons if there are
        if ($addons_activated) new \Wappointment\Services\Wappointment\VersionCheck;
        add_action('admin_init', [$this, 'enqueueMin']);
        add_action('admin_menu', [$this, 'registerMenuRoot']);
        add_action('admin_notices', ['\Wappointment\WP\Alerts', 'display']);
        if ($isInstalledAndUpdated) {
            add_action('admin_menu', [$this, 'registerMenuSubs']);
        }
        if (WPHelpers::isPluginPage()) {
            add_action('admin_init', [$this, 'enqueueBackendPlugin']);
        }
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
        wp_enqueue_style(WAPPOINTMENT_SLUG . '-wap', plugins_url(WAPPOINTMENT_SLUG . '/dist/css/wappointments.css'));
    }

    public function enqueueBackendPlugin()
    {
        wp_register_script(WAPPOINTMENT_SLUG . '_backend', Helpers::assetUrl('main.js'), ['jquery'], null, true);

        wp_register_script(WAPPOINTMENT_SLUG . '_backend_menu', Helpers::assetUrl('js/backend_menu.js'), [], null, true);
        wp_localize_script(WAPPOINTMENT_SLUG . '_backend_menu', WAPPOINTMENT_SLUG . 'Admin', [
            'wizardStep' => Status::wizardStep(),
            'updatePage' => Status::isReadyForUpdatePage()
        ]);

        wp_enqueue_script(WAPPOINTMENT_SLUG . '_backend_menu');
        wp_enqueue_script(WAPPOINTMENT_SLUG . '_backend');
    }

    public function enqueueBackendAlways()
    {
        if (!\WappointmentLv::function_exists('register_block_type')) {
            // Gutenberg is not active.
            return;
        }
        wp_register_script(WAPPOINTMENT_SLUG . 'block-gutenberg', Helpers::assetUrl('js/gutenberg/block-gutenberg.js'), ['wp-blocks', 'wp-i18n', 'wp-element'], null, true);
        wp_enqueue_script(WAPPOINTMENT_SLUG . 'block-gutenberg');

        register_block_type('gutenberg-examples/example-01-basic', [
            'editor_script' => 'block-gutenberg',
        ]);
    }
}
