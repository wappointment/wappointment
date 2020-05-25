<?php

namespace Wappointment\WP;

class WidgetAPI
{

    public static function getSidebars()
    {
        global $wp_registered_sidebars;
        return $wp_registered_sidebars;
    }

    public static function getWidgets()
    {
        global $wp_registered_widgets;
        return $wp_registered_widgets;
    }


    public static function pushTo($sidebar)
    {
        $sidebars =  get_option('sidebars_widgets');

        if (is_array($sidebars[$sidebar]) && !empty($sidebars[$sidebar])) {
            $sidebars[$sidebar][] = 'wappointment';
            update_option('sidebars_widgets', $sidebars);
        }
    }
}
