<?php

namespace Wappointment\WP;

use Wappointment\Services\Settings;

class Database
{
    public $host = '';
    public $charset = '';
    public $collate = '';
    private $port = '3306';
    private $prefix = '';
    private $mainprefix = '';

    public function __construct()
    {
        global $wpdb;

        $this->host = DB_HOST;
        $this->prefix = $wpdb->prefix;
        if (is_multisite() && defined('BLOG_ID_CURRENT_SITE')) {
            $this->mainprefix = $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE);
        }

        if (strpos($this->host, ':') !== false) {
            $host_port = explode(':', $this->host);
            $this->host = $host_port[0];
            $this->port = $host_port[1];
        } else {
            if (Settings::get('alt_port') && !empty($this->getAltPort())) {
                $this->port = $this->getAltPort(); // make sure this cannot break working connection
            }
        }

        $charset_collate = $wpdb->determine_charset($wpdb->charset, $wpdb->collate);
        $this->charset =  !empty($charset_collate['charset']) ? $charset_collate['charset'] : $wpdb->charset;
        $this->collate =  !empty($charset_collate['collate']) ? $charset_collate['collate'] : $wpdb->collate;
    }

    public function getAltPort()
    {
        return ini_get('mysqli.default_port');
    }
    public function getDbName()
    {
        return DB_NAME;
    }

    public function getDbUser()
    {
        return DB_USER;
    }

    public function getDbPass()
    {
        return DB_PASSWORD;
    }

    public function getDbCollate()
    {
        return $this->collate;
    }

    public function getDbCharset()
    {
        return $this->charset;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }
    public function getMainPrefix()
    {
        return $this->mainprefix;
    }
}
