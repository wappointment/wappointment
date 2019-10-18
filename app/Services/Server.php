<?php

namespace Wappointment\Services;


class Server
{

    public static function data()
    {
        return [
            'wp' => [
                'ms' => is_multisite(),
                'version' => get_bloginfo('version'),
                'debug' => defined('WP_DEBUG') ? WP_DEBUG : false,
                'active_plugins' => self::getActivePlugins()
            ],
            'db' => [
                'charset' => defined('DB_CHARSET') ? DB_CHARSET : false,
                'collate' => defined('DB_COLLATE') ? DB_COLLATE : false
            ],
            'php' => [
                'version' => PHP_VERSION,
                'os' => PHP_OS,
                'extensions' => get_loaded_extensions()
            ],
            'cron' => [
                'web_cron_disabled' => defined('DISABLE_WP_CRON') ? DISABLE_WP_CRON : false,
                'statuses' => [
                    'wappo' => \Wappointment\WP\Scheduler::getStatuses(),
                    'all_jobs' => _get_cron_array()
                ]
            ]
        ];
    }

    private static function getActivePlugins()
    {
        $plugins = ['site' => get_option('active_plugins')];
        if (is_multisite()) {
            $plugins['ms_wide'] = get_option('active_sitewide_plugins');
        }
        return $plugins;
    }
}
