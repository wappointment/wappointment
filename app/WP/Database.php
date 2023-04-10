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

        $this->setCharsetAndCollate($wpdb);

    }

    private function setCharsetAndCollate($wpdb)
    {
        
        if(!empty($wpdb->charset) && !empty($wpdb->collate)){
            $this->charset = $wpdb->charset;
            $this->collate = $wpdb->collate;
            return;
        }
        if(empty(Settings::get('collate'))){
            $this->findCharsetCollate($wpdb);
        }
            
        $this->charset = Settings::get('charset');
        $this->collate = Settings::get('collate');
    }

    private function findCharsetCollate($wpdb)
    {
        //determine collate
        $prefix = is_multisite() ? $this->getMainPrefix():$this->getPrefix();
        $dbResult=$wpdb->get_results('show create table '.$prefix.'wappo_appointments');
        $responseString='';
        foreach($dbResult[0] as $res){
            $responseString.=$res;
        }

        if(preg_match('/(?>CHARSET=)(.*) /', $responseString, $charsetMatch)){
            Settings::save('charset', trim($charsetMatch[1]));
        }
        if(preg_match('/(?>COLLATE=)(.*)/', $responseString, $collateMatch)){
            Settings::save('collate', trim($collateMatch[1]));
        }
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
