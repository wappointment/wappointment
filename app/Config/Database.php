<?php

namespace Wappointment\Config;

class Database
{
    public static $prefix_self = 'wappo';
    private static $capsule = null;

    public static function capsule($alt_port = false)
    {
        if (is_null(self::$capsule)) {
            self::$capsule = new \Illuminate\Database\Capsule\Manager();

            self::$capsule->addConnection(self::config($alt_port));
            if (is_multisite()) {
                self::$capsule->addConnection(self::configms($alt_port), 'ms');
            }

            self::$capsule->setAsGlobal();
            self::$capsule->bootEloquent();
        }

        return self::$capsule;
    }

    public static function resetCapsule()
    {
        self::$capsule = null;
    }

    public static function getWpSitePrefix()
    {
        return self::config()['prefix'];
    }

    private static function config($alt_port = false)
    {
        $db = new \Wappointment\WP\Database();

        $config = [
            'driver' => 'mysql',
            'database' => $db->getDbName(),
            'username' => $db->getDbUser(),
            'password' => $db->getDbPass(),
            'charset' => $db->getDbCharset(),
            'collation' => $db->getDbCollate(),
            'prefix' => $db->getPrefix(),
            'strict' => true,
            'engine' => null,
            /*'modes' => [
                //'ONLY_FULL_GROUP_BY',
                'STRICT_TRANS_TABLES',
                'NO_ZERO_IN_DATE',
                'NO_ZERO_DATE',
                'ERROR_FOR_DIVISION_BY_ZERO',
                //'NO_AUTO_CREATE_USER',
                'NO_ENGINE_SUBSTITUTION'
            ]*/
        ];

        if (is_numeric($db->getPort()) || strpos($db->getPort(), '/') !== 0) {
            $config['host'] = $db->getHost();
            $config['port'] = $alt_port ? $db->getAltPort() : $db->getPort();
        } else {
            $config['unix_socket'] = $db->getPort();
        }

        return $config;
    }

    private static function configms($alt_port = false)
    {
        $db = new \Wappointment\WP\Database();
        $config = self::config($alt_port);
        $config['prefix'] = $db->getMainPrefix();
        return $config;
    }
}
