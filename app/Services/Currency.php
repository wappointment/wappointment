<?php

namespace Wappointment\Services;

use Wappointment\Helpers\Get;

class Currency
{

    public static function get()
    {
        $currencies = Get::list('currencies');
        $code = Settings::get('currency');
        foreach ($currencies as $currency) {
            if ($currency['code'] == $code) {
                return $currency;
            }
        }
    }
}
