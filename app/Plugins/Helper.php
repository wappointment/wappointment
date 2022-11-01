<?php

namespace Wappointment\Plugins;

use Wappointment\Helpers\Site;
use Wappointment\Plugins\Contract\PluginDefinition;

class Helper
{
    public static function active($plugin_fullname)
    {
        static::requireWpPlugins();
        return \is_plugin_active($plugin_fullname);
    }

    public static function plugins()
    {
        static::requireWpPlugins();
        return \get_plugins();
    }

    public static function getPlugin($type)
    {
        $definition = static::getDefinition($type);

        //resolve if already loaded
        $instance = Site::container()->resolve($definition->contract());
        if (!empty($instance)) {
            return $instance;
        }

        return static::generateBinding($definition);
    }

    private static function getDefinition($type):PluginDefinition
    {
        $class = __NAMESPACE__.'\\'.$type.'\\Definition';
        if(!class_exists($class)){
            throw new \WappointmentException("Cannot find plugin definition ".$type, 1);
            
        }
        return new $class();
    }

    private static function generateBinding(PluginDefinition $definition)
    {
        foreach ($definition->implementations() as $pluginName => $className) {
            if ($pluginName === 'default' || static::active($pluginName)) {
                $instance = new $className();
                Site::container()->bind($definition->contract(), $instance);
                return $instance;
            }
        }
    }

    private static function requireWpPlugins()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
    }
}
