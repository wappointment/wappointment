<?php

namespace Wappointment\WP;

class PluginsDetection
{

    public static function smtpConfigured()
    {
        if (defined('WPMS_PLUGIN_VER')) {
            return [
                'config' => 'admin.php?page=wp-mail-smtp',
                'icon' => 'wp-mail-smtp/assets/images/logo.svg',
                'name' => 'WP Mail SMTP',
            ];
        }
        if (defined('POST_SMTP_VER')) {
            return [
                'config' => 'admin.php?page=postman',
                'icon' => 'post-smtp/style/images/badge.png',
                'name' => 'Post SMTP',
            ];
        }
        if (defined('HAET_MAIL_PATH')) {
            return [
                'config' => 'options-general.php?page=wp-html-mail',
                'icon' => 'https://ps.w.org/wp-html-mail/assets/icon-128x128.png?rev=1730334',
                'name' => 'WP HTML Mail',
            ];
        }

        if (defined('FLUENTMAIL_PLUGIN_VERSION')) {
            return [
                'config' => 'options-general.php?page=fluent-mail',
                'icon' => 'fluent-smtp/assets/images/logo.svg',
                'name' => 'Fluent SMTP',
            ];
        }
        return false;
    }

    public static function createsEmailConflict()
    {
        if (defined('WP_PGP_ENCRYPTED_EMAILS_MIN_PHP_VERSION')) {
            return [
                'name' => 'WP PGP Encrypted Emails',
            ];
        }

        return false;
    }
}
