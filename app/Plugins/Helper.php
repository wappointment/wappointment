<?php

namespace Wappointment\Plugins;

use Wappointment\Helpers\Get;
use Wappointment\Helpers\Site;

class Helper
{
    public static function active($plugin_fullname)
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return \is_plugin_active($plugin_fullname);
    }

    public static function getPlugin($type)
    {
        $definition = Get::list('plugins_'.$type);
        $instance = Site::container()->resolve($definition['contract']);
        if (!empty($instance)) {
            return $instance;
        }

        return static::generateBinding($definition);
    }

    private static function generateBinding($definition)
    {
        foreach ($definition['implementations'] as $pluginName => $className) {
            if ($pluginName === 'default' || static::active($pluginName)) {
                $instance = new $className();
                Site::container()->bind($definition['contract'], $instance);
                return $instance;
            }
        }
    }
}
