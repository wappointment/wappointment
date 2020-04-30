<?php

namespace Wappointment\WP;

/** */
class Shortcodes
{
    public function __construct()
    {
        add_shortcode('wap_widget', ['\Wappointment\WP\Shortcodes', 'wapWidgetHandler']);
    }

    public static function wapWidgetHandler($atts)
    {
        $a = [];
        if (is_array($atts)) {
            $a = shortcode_atts([
                'auto_open' => in_array('open', $atts),
                'large_version' => in_array('large', $atts),
                'button_title' => !empty($atts['title']) ?
                    $atts['title'] : (new \Wappointment\Services\WidgetSettings)->get()['button']['title'],
            ], $atts);
        }


        return Widget::baseHtml($a);
    }
}
