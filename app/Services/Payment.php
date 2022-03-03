<?php

namespace Wappointment\Services;

use Wappointment\Helpers\Get;

class Payment
{

    public static function currency()
    {
        static $currency_loaded = false;
        if ($currency_loaded !== false) {
            return $currency_loaded;
        }
        foreach (static::currencies() as $currency) {
            if ($currency['code'] == static::currencyCode()) {
                $currency['format'] = static::getFormat($currency);
                $currency_loaded = $currency;
                return $currency;
            }
        }
    }

    public static function active()
    {
        return !static::isWooActive() && static::atLeastOneMethodIsActive();
    }

    public static function currencyCode()
    {
        return Settings::get('currency');
    }

    public static function currencies()
    {
        return Get::list('currencies');
    }

    public static function getFormat($currency)
    {
        switch ($currency['position']) {
            case 1:
                return '[price][currency]';
            case 2:
                return '[currency][price]';
            case 3:
                return '[price] [currency]';
            case 4:
                return '[currency] [price]';
        }
    }

    public static function formatPrice($price)
    {
        $currency = static::currency();
        return str_replace(['[price]', '[currency]'], [number_format($price, 2), $currency['symbol']], $currency['format']);
    }


    public static function methods()
    {
        $methods = [
            [
                'key' => 'onsite',
                'name' => __('On Site', 'wappointment'),
                'description' => __('Customers pay you in person at your business\' address or wherever you deliver the service', 'wappointment'),
                'installed' => true,
                'active' => Settings::get('onsite_enabled'),
            ],
            [
                'key' => 'stripe',
                'name' => 'Stripe',
                'description' => __('Customers pay online with their VISA, Mastercard, Amex etc ... in 44 countries and 135 currencies', 'wappointment'),
                'installed' => false,
                'hideLabel' => true,
                'active' => false,
                'cards' => ['visa', 'mastercard', 'amex']
            ],
            [
                'key' => 'paypal',
                'name' => 'Paypal',
                'description' => __('Customers pay online with their Paypal Account, VISA, Mastercard, Amex etc ... in 25 currencies and 200 countries', 'wappointment'),
                'installed' => false,
                'hideLabel' => true,
                'active' => false,
                'cards' => ['visa', 'mastercard', 'amex']
            ],
            [
                'key' => 'woocommerce',
                'name' => 'WooCommerce',
                'description' => __('WooCommerce is the most popular ecommerce plugin for WordPress. Already familiar with WooCommerce? Then selling your time with Wappointment and WooCommerce will be really easy.', 'wappointment'),
                'installed' => false,
                'hideLabel' => true,
                'active' => false,
            ],
        ];

        return apply_filters('wappointment_payment_methods', static::orderMethods($methods));
    }

    public static function orderMethods($methods)
    {
        $ordered_methods = [];
        $methods_order = Settings::get('payments_order');

        foreach ($methods_order as $payment_key) {
            foreach ($methods as $key => $method) {
                if ($method !== false && $method['key'] == $payment_key) {
                    $ordered_methods[] = $method;
                    $methods[$key] = false;
                }
            }
        }
        // push the methods left that were not appearing in the order array
        foreach ($methods as $method) {
            if ($method !== false) {
                $ordered_methods[] = $method;
            }
        }
        return $ordered_methods;
    }

    public static function isWooActive()
    {
        $methods = static::methods();

        return count($methods) < 2 && $methods[0]['key'] == 'woocommerce';
    }

    public static function atLeastOneMethodIsActive()
    {
        $methods = static::methods();
        foreach ($methods as $method) {
            if ($method['active']) {
                return true;
            }
        }
    }
}
