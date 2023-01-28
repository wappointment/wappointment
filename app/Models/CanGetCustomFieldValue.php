<?php

namespace Wappointment\Models;

trait CanGetCustomFieldValue
{
    private function getCustomFieldFormattedValue($tag)
    {
        $cfs = \WappointmentAddonServices\Services\CustomFields::get();
        $optValue = $this->options[$tag['key']];
        foreach ($cfs as $custom_field) {
            if ($tag['key'] === $custom_field['namekey']) {
                if (!empty($custom_field['is_address'])) {
                    $optValue = static::getGoogleLink($this->options[$tag['key']]);
                }
                return static::cfValueToString($optValue, $custom_field);
            }
        }
        return $this->options[$tag['key']].'-NOT-MATCHED';
    }

    public static function getGoogleLink($address)
    {
        return '<a href="https://www.google.com/maps/search/?api=1&query=' . urlencode($address) . '" target="_blank">' . nl2br($address) . '</a>';
    }

    protected static function cfValueToString($cfValue, $customfield)
    {
        return is_array($cfValue) ? implode(', ', static::convertKeysToLabels($cfValue, $customfield)) : static::convertKeyToLabel($cfValue, $customfield);
    }

    protected static function convertKeysToLabels($cfValue, $customfield)
    {
        foreach ($cfValue as $key => $value) {
            $cfValue[$key] = static::convertNamekeyToLabel($cfValue[$key], $customfield['values']);
        }
        return $cfValue;
    }

    protected static function convertKeyToLabel($cfValue, $customfield)
    {
        if ($customfield['type'] == 'checkbox') {
            return static::convertCheckboxValueToText($cfValue);
        }
        if (!empty($customfield['values'])) {
            return static::convertNamekeyToLabel($cfValue, $customfield['values']);
        }

        return $cfValue;
    }

    protected static function convertCheckboxValueToText($cfValue)
    {
        return (int)$cfValue > 0 ? __('Yes', 'wappointment') : __('No', 'wappointment');
    }

    protected static function convertNamekeyToLabel($cfValue, $values)
    {
        foreach ($values as $keyLabelObject) {
            if ($keyLabelObject['value'] == $cfValue) {
                return $keyLabelObject['label'];
            }
        }
        return $cfValue;
    }
}
