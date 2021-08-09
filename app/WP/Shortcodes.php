<?php

namespace Wappointment\WP;

/** */
class Shortcodes
{
    public function __construct()
    {
        add_shortcode('wap_widget', ['\Wappointment\WP\Shortcodes', 'wapWidgetHandler']);
        add_shortcode('wap_history', ['\Wappointment\WP\Shortcodes', 'wapAppointmentHistory']);
        add_shortcode('wappointment_page', ['\Wappointment\WP\CustomPage', 'getPageContent']);
    }

    public static function wapAppointmentHistory($atts)
    {
        return AppointmentHistory::render($atts);
    }

    public static function wapWidgetHandler($atts)
    {
        $fetched_attributes = [];

        if (is_array($atts)) {
            $fetched_attributes = shortcode_atts(
                apply_filters('wappointment_shortcode_attributes', static::handleFilters($atts), $atts),
                $atts
            );
            if (!empty($fetched_attributes['stafff_page']) && isset($fetched_attributes['staff_selection'])) {
                unset($fetched_attributes['staff_selection']);
            }
        }

        return Widget::baseHtml($fetched_attributes);
    }

    public static function handleFilters($atts)
    {
        $params = [
            'center' => in_array('center', $atts),
            'auto_open' => in_array('open', $atts),
            'large_version' => in_array('large', $atts),
            'popup' => in_array('popup', $atts),
            'pop_off' => in_array('pop_off', $atts),
            'auto_pop' => in_array('pop', $atts),
            'week' => in_array('week', $atts),
            'button_title' => !empty($atts['title']) ?
                $atts['title'] : (new \Wappointment\Services\WidgetSettings)->get()['button']['title'],
        ];
        if (in_array('staff_page', $atts)) {
            $params['staff_page'] = true;
        }

        return $params;
    }
}
