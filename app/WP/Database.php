<?php

namespace Wappointment\WP;

class Database
{
    public $host = '';
    private $port = '';
    private $prefix = '';
    private $mainprefix = '';

    public function __construct()
    {
        global $wpdb;

        $this->host = DB_HOST;
        $this->port = '3306';
        $this->prefix = $wpdb->prefix;
        if (defined('BLOG_ID_CURRENT_SITE')) {
            $this->mainprefix = $wpdb->get_blog_prefix(BLOG_ID_CURRENT_SITE);
        }

        if (strpos($this->host, ':') !== false) {
            $host_port = explode(':', $this->host);
            $this->host = $host_port[0];
            $this->port = $host_port[1];
        }
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
        return empty(DB_COLLATE) ? 'utf8_general_ci' : DB_COLLATE;
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
