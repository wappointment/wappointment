<?php

namespace Wappointment\Plugins;

use Wappointment\Helpers\Get;
use Wappointment\Helpers\Site;

class Helper
{
    public static function active($plugin_fullname){
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return \is_plugin_active($plugin_fullname);
    }

    public static function getPlugin($implementation, $type){
        $instance = Site::container()->resolve($implementation);
        if(!empty($instance)){
            return $instance;
        }
        static::generateBinding($implementation, $type);
        return Site::container()->resolve($implementation);
    }

    private static function generateBinding($implementation, $type)
    {
        foreach (Get::list('plugins_'.$type) as $pluginName => $className) {
            if($pluginName === 'default' || static::active($pluginName)){
                Site::container()->bind($implementation, new $className());
            }
        }
    }
}

