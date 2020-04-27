<?php

namespace Wappointment\Addons;

abstract class AbstractBoot implements Boot
{
    public static $addon_key;
    public static $addon_name;
    public static $name_space;
    public static $addon_version;
    public static $addon_settings;
    public static $addon_settings_icon;
    public static $has_settings;
    public static $has_back_setup;
    public static $has_front_script;

    public static function getAddonSlug()
    {
        return str_replace('_', '-', static::$addon_key);
    }

    public static function init()
    {
        add_filter('wappointment_addon_wrapper_' . static::$addon_key, [static::$name_space . 'Services\\Addon', 'filterWrapper']);
        if (!\Wappointment\System\Status::isInstalled() || !static::canRun()) return;

        //only triggerred once the plugin is ready to be used
        static::installedFilters();
    }

    public static function canRun()
    {
        return true;
    }

    public static function isInstalled()
    {
        return call_user_func(static::$name_space . 'Services\\Settings::get', 'installed_at');
        //return Settings::get('installed_at');
    }

    public static function backSetup()
    {
        wp_enqueue_script(static::$addon_key . '_back_setup', plugins_url(static::getAddonSlug() . '/dist/back-setup.js'), [], static::$addon_version, true);
    }

    public static function adminInit()
    {

        if (\Wappointment\WP\Helpers::isPluginPage()) {
            if (static::$has_back_setup) {
                static::backSetup();
            }

            if (static::isInstalled()) {
                wp_enqueue_script(static::$addon_key . '_back', plugins_url(static::getAddonSlug() . '/dist/back.js'), [], static::$addon_version, true);

                if (static::$has_front_script) {
                    self::frontEnqueue();
                }
            }
        }
    }

    public static function frontEnqueue()
    {
        wp_enqueue_script(static::$addon_key . '_front', plugins_url(static::getAddonSlug() . '/dist/front.js'), [], static::$addon_version, true);
    }

    public static function hooksAndFiltersWhenInstalled()
    {
        add_filter('wappointment_viewdata_' . static::$addon_settings, [static::$name_space . 'Boot', 'getMainSettings']);
        if (static::$has_front_script) {
            add_action('wappointment_enqueue_front_' . static::$addon_key, ['\\WappointmentAddonWoocommerce\\Boot', 'frontEnqueue']);
        }
    }

    public static function hooksAndFiltersAlways()
    {
        // needed when not installed yet
        add_filter('wappointment_active_addons', [static::$name_space . 'Boot', 'registerAddon']);
        add_filter('wappointment_js_vars', [static::$name_space . 'Boot', 'jsVariables']);
        add_action('admin_init', [static::$name_space . 'Boot', 'adminInit'], 11);
    }

    public static function registerAddon($addons)
    {

        $addons[static::$addon_key] = [
            'name' => static::$addon_name,
            'icon' => static::$addon_settings_icon,
            'settings' => (bool) static::$has_settings
        ];
        return $addons;
    }

    public static function jsVariables($var)
    {
        $variables = [
            'installed_at' => static::isInstalled(),
        ];

        return $var .= 'var wappointment' . static::$addon_key . ' = ' . json_encode($variables) . ";\n";
    }
}
