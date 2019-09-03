<?php

namespace Wappointment\System;


class InitPreinstall
{
    private $plugin_file = '';
    public function __construct()
    {
        $this->plugin_file = WAPPOINTMENT_SLUG . DIRECTORY_SEPARATOR . 'index.php';
        register_activation_hook($this->plugin_file, [$this, 'activated']);
        add_action('admin_init', [$this, 'checkJustActivated']);
        add_filter('plugin_row_meta', [$this, 'custom_plugin_row_meta'], 10, 2);
        new \Wappointment\Routes\Init();
    }

    function activated()
    {
        add_option('wappo_plug_activated', 'wappointment');
    }

    function checkJustActivated()
    {
        if (get_option('wappo_plug_activated') == 'wappointment') {
            wp_enqueue_style(WAPPOINTMENT_SLUG . '-wap', plugins_url(WAPPOINTMENT_SLUG . '/dist/css/wappointments.css'));
            add_action('wp_print_scripts', [$this, 'scrollDownToUs']);
            delete_option('wappo_plug_activated');
        }
    }

    public function scrollDownToUs()
    {
        $return = '<script type="text/javascript">' . "\n";
        $return .= 'window.addEventListener("DOMContentLoaded", (event) => {
            let row_wappo = document.querySelectorAll(\'[data-slug="wappointment"]\')[0]
            row_wappo.classList.add("just-activated")
            row_wappo.scrollIntoView({block: "center", behavior: "smooth"})
        });';
        $return .= '</script>' . "\n";
        echo $return;
    }

    function custom_plugin_row_meta($links, $file)
    {

        if (strpos($file, $this->plugin_file) !== false) {
            $buttonInit = '<a href="/wp-admin/admin.php?page=wappointment_calendar" class="button button-primary button-large" >Initial Setup</a>';
            $htmlWrap = '<div class="notice inline notice-info">
            <p>Alright! Thanks for activating me, now let\'s go through my initial setup ' . $buttonInit . '
            </p></div>';
            $links = [
                'initial_setup' => $htmlWrap
            ];
        }


        return $links;
    }
}
