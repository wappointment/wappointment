<?php

namespace Wappointment\Formatters;

use Wappointment\Managers\Central;

class CustomFields
{
    public static function get()
    {
        static $customFields = false;
        if ($customFields === false) {
            $customFields = Central::get('CustomFields')::get();
        }
        return $customFields;
    }

    public static function keyLabels()
    {
        $keyLabels = [];
        $source = static::get();
        foreach ($source as $customField) {
            $keyLabels[$customField['namekey']] = $customField['name'];
        }
        return $keyLabels;
    }
}
