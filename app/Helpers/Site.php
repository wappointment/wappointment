<?php

namespace Wappointment\Helpers;

use Wappointment\Plugins\Helper;
use Wappointment\System\Container;

class Site
{
    /**
     * we've create the helpr to wrap the get_locale with logic if needed
     *
     * @return void
     */
    public static function locale()
    {
        return get_locale();
    }

    public static function languages()
    {
        return static::MultiLang()->languages();
    }

    public static function MultiLang()
    {
        return Helper::getPlugin('multilang');
    }

    public static function container()
    {
        static $container = false;

        if ($container === false) {
            $container = new Container();
        }
        return $container;
    }
}
