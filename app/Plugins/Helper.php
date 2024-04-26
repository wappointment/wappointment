<?php

namespace Wappointment\Plugins;

use Wappointment\Helpers\Site;
use Wappointment\Plugins\Contract\PluginDefinition;
use Wappointment\WP\Plugins;
// @codingStandardsIgnoreFile
class Helper
{
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

    private static function getDefinition($type): PluginDefinition
    {
        $class = __NAMESPACE__.'\\'.$type.'\\Definition';
        if (!class_exists($class)) {
            throw new \WappointmentException("Cannot find plugin definition ".$type, 1);
        }
        return new $class();
    }

    private static function generateBinding(PluginDefinition $definition)
    {
        foreach ($definition->implementations() as $pluginName => $className) {
            if ($pluginName === 'default' || Plugins::wp()->active($pluginName)) {
                $instance = new $className();
                Site::container()->bind($definition->contract(), $instance);
                return $instance;
            }
        }
    }
}
