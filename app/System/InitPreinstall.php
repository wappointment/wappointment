<?php

namespace Wappointment\System;

use Wappointment\WP\Helpers as WPHelpers;

class InitPreinstall
{
    private $plugin_file = '';
    public function __construct()
    {
        $this->plugin_file = WAPPOINTMENT_SLUG . DIRECTORY_SEPARATOR . 'index.php';
        register_activation_hook($this->plugin_file, [$this, 'activated']);
        add_action('admin_init', [$this, 'checkJustActivated']);
        add_filter('plugin_row_meta', [$this, 'customPluginRowMeta'], 10, 2);

        add_filter('plugin_action_links_' . plugin_basename(WAPPOINTMENT_FILE), [$this, 'customPluginLinks']);

        new \Wappointment\Routes\Init();
    }

    public function activated()
    {
        add_option('wappo_plug_activated', 'wappointment');
    }

    public function checkJustActivated()
    {
        if (get_option('wappo_plug_activated') == 'wappointment') {
            wp_enqueue_style(WAPPOINTMENT_SLUG . '-wap', plugins_url(WAPPOINTMENT_SLUG . '/dist/css/wappointment.css'));
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

    public function customPluginRowMeta($links, $file)
    {

        if (strpos($file, $this->plugin_file) !== false) {
            $buttonInit = '<a href="' .
                WPHelpers::adminUrl('wappointment_calendar') .
                '" class="button button-primary button-large" >Setup</a>';
            $htmlWrap = '<div class="notice inline notice-info">
            <p>Thanks for activating me! Now set me up in only few seconds ' . $buttonInit . '
            </p></div>';
            $links = [
                'initial_setup' => $htmlWrap
            ];
        }


        return $links;
    }

    public function customPluginLinks($links)
    {
        $links[] = '<a href="' . esc_url(WPHelpers::adminUrl('wappointment_calendar')) . '" >Start Setup</a>';
        return $links;
    }
}
