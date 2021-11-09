<?php

namespace Wappointment\Helpers;

class Translations
{

    public function woo()
    {
        __('Booking starts at', 'wappointment');
        __('The appointment is pending. It will be confirmed once the client completes the payment in WooCommerce.', 'wappointment');
        /* translators: %s - number of minutes  */
        __('If the order is not completed within %s minutes, the slot will be made available again automatically.', 'wappointment');
    }

    public static function get($key, $params = [])
    {
        $translation = static::fetchTranslations()[$key];

        return is_array($params) && !empty($params) ? vsprintf($translation, $params) : $translation;
    }

    public static function fetchTranslations()
    {
        static $translations = false;
        if ($translations === false) {
            $translations = Get::list('translations');
        }
        return $translations;
    }
}
