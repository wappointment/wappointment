<?php

namespace Wappointment\WP;

use Wappointment\Helpers\Site;

class Plugins
{
    public function __construct()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
    }

    public static function wp()
    {
        return Site::singleton(__CLASS__);
    }

    public function active($pluginEntryPoint)
    {
        return \is_plugin_active($pluginEntryPoint);
    }

    public function list()
    {
        return \get_plugins();
    }

    public function installed($pluginEntryPoint)
    {
        return !empty($this->list()[$pluginEntryPoint]);
    }

    public function activate($pluginEntryPoint)
    {
        $this->canActivate();
        return \activate_plugin($pluginEntryPoint);
    }

    public function deactivate($pluginEntryPoint)
    {
        $this->canActivate();
        return \deactivate_plugins($pluginEntryPoint);
    }

    private function canActivate()
    {
        if (!current_user_can('activate_plugins')) {
            throw new \WappointmentException('Sorry, you are not allowed to activate plugins on this site.');
        }
    }
}
