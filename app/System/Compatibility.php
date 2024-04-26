<?php

namespace Wappointment\System;

use Wappointment\Services\Settings;
// phpcs:ignoreFile
class Compatibility
{

    public static function getRestUrl()
    {
        if (!empty($_GET['wappo_ugly_permalinks'])) {
            Settings::save('force_ugly_permalinks', true);
        }
        return esc_url_raw(static::requiresUgly() ? static::getUglyRestUrl() : rest_url());
    }

    protected static function requiresUgly()
    {
        return Settings::get('force_ugly_permalinks') || empty(get_option('permalink_structure'));
    }

    /**
     * taken from wp and modified
     *
     * @param [type] $blog_id
     * @param string $path
     * @param string $scheme
     * @return void
     */
    protected static function getUglyRestUrl($blog_id = null, $path = '/', $scheme = 'rest')
    {
        if (empty($path)) {
            $path = '/';
        }

        $path = '/' . ltrim($path, '/');

        $url = trailingslashit(get_home_url($blog_id, '', $scheme));
        // nginx only allows HTTP/1.0 methods when redirecting from / to /index.php.
        // To work around this, we manually add index.php to the URL, avoiding the redirect.
        if ('index.php' !== substr($url, 9)) {
            $url .= 'index.php';
        }

        $url = add_query_arg('rest_route', $path, $url);

        if (is_ssl() && isset($_SERVER['SERVER_NAME'])) {
            // If the current host is the same as the REST URL host, force the REST URL scheme to HTTPS.
            if (wp_parse_url(get_home_url($blog_id), PHP_URL_HOST) === $_SERVER['SERVER_NAME']) {
                $url = set_url_scheme($url, 'https');
            }
        }

        if (is_admin() && force_ssl_admin()) {
            /*
                 * In this situation the home URL may be http:, and `is_ssl()` may be false,
                 * but the admin is served over https: (one way or another), so REST API usage
                 * will be blocked by browsers unless it is also served over HTTPS.
                 */
            $url = set_url_scheme($url, 'https');
        }
        return apply_filters('rest_url', $url, $path, $blog_id, $scheme);
    }
}
