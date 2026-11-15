<?php
/*
 * Test harness for the disposable legacy-master oracle site only.
 * It never changes booking logic: it keeps queued jobs observable by stopping the
 * WordPress cron loopback, and records outgoing mail instead of sending it.
 */
add_filter('pre_http_request', static function ($response, $arguments, $url) {
    return strpos((string) $url, 'wp-cron.php') === false
        ? $response
        : ['headers' => [], 'body' => '', 'response' => ['code' => 200, 'message' => 'OK'], 'cookies' => [], 'filename' => null];
}, 10, 3);

add_action('init', static function () {
    remove_action('init', ['\\Wappointment\\WP\\Helpers', 'enqueueFrontScripts'], 98);
}, 97);

add_filter('pre_wp_mail', static function ($sent, $attributes) {
    $log = get_option('wappo_oracle_mail', []);
    $log[] = ['to' => $attributes['to'], 'subject' => $attributes['subject']];
    update_option('wappo_oracle_mail', $log, false);
    return true;
}, 10, 2);
