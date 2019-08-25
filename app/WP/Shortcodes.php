<?php

namespace Wappointment\WP;

/** */
class Shortcodes
{
    public function __construct()
    {
        add_shortcode('wap_widget', ['\Wappointment\WP\Shortcodes', 'wap_widget_handler']);
    }

    public static function wap_widget_handler($atts)
    {
        $a = shortcode_atts([
            'button' => !empty($atts['title']) ? $atts['title'] : (new \Wappointment\Services\WidgetSettings)->get()['button']['title'],
        ], $atts);

        return Widget::baseHtml($a['button']);
    }
}
