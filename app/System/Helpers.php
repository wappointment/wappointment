<?php

namespace Wappointment\System;
// @codingStandardsIgnoreFile
class Helpers
{
    public static function isProd()
    {
        return \WappointmentLv::isTest() === false;
    }

    public static function wappointmentLink($medium = 'email', $campaign = 'daily_email')
    {
        return 'https://wappointment.com?utm_source=plugin&utm_medium=' . $medium . '&utm_campaign=' . $campaign;
    }

    public static function pluginName()
    {
        return WAPPOINTMENT_NAME;
    }

    public static function pluginSlug()
    {
        return strtolower(self::pluginName());
    }

    public static function pluginPath()
    {
        return WAPPOINTMENT_PATH;
    }

    public static function pluginUrl($url = '')
    {
        return plugins_url(self::pluginSlug()) . $url;
    }

    public static function assetUrl($asset_name)
    {
        return self::pluginUrl() . '/dist/' . self::assetGetBundledName($asset_name);
    }

    private static function assetGetBundledName($bundled_file)
    {
        static $manifests = [];

        $manifestPath = self::pluginPath() . 'dist/manifest.json';

        if (!isset($manifests[$manifestPath])) {
            if (!file_exists($manifestPath)) {
                throw new \WappointmentException('The manifest does not exist.');
            }
            if (!is_readable($manifestPath)) {
                throw new \WappointmentException('The manifest is not readable.');
            }

            $manifests[$manifestPath] = json_decode(wp_remote_get($manifestPath), true);
        }

        $manifest = $manifests[$manifestPath];

        if (!isset($manifest[$bundled_file])) {
            if (file_exists(WAPPOINTMENT_PATH . '/dist/' . $bundled_file)) {
                return $bundled_file;
            }
            throw new \WappointmentException("Unable to locate bundled file: {$bundled_file}.");
        }

        return $manifest[$bundled_file];
    }
}
