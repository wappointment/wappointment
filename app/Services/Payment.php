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
        return str_replace(['[price]', '[currency]'], [$price, $currency['symbol']], $currency['format']);
    }


    public static function methods()
    {
        $methods = [
            [
                'key' => 'onsite',
                'name' => 'On Site',
                'description' => 'Customers pay you in person at your business\' address or wherever you deliver the service',
                'installed' => true,
                'active' => Settings::get('onsite_enabled'),
            ],
            [
                'key' => 'stripe',
                'name' => 'Stripe',
                'description' => 'Customers pay online with their VISA, Mastercard, Amex etc ... in 44 countries and 135 currencies',
                'installed' => false,
                'hideLabel' => true,
                'active' => false,
                'cards' => ['visa', 'mastercard', 'amex']
            ],
            [
                'key' => 'paypal',
                'name' => 'Paypal',
                'description' => 'Customers pay online with their Paypal Account, VISA, Mastercard, Amex etc ... in 25 currencies and 200 countries',
                'installed' => false,
                'hideLabel' => true,
                'active' => false,
                'cards' => ['visa', 'mastercard', 'amex']
            ],
            [
                'key' => 'woocommerce',
                'name' => 'WooCommerce',
                'description' => 'WooCommerce is the most popular ecommerce plugin for WordPress. Already familiar with WooCommerce? Then selling your time with Wappointment and WooCommerce will be real easy.',
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

    protected static function atLeastOneMethodIsActive()
    {
        $methods = static::methods();
        foreach ($methods as $method) {
            if ($method['active']) {
                return true;
            }
        }
    }
}
