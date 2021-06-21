<?php

namespace Wappointment\Services;

use Wappointment\Helpers\Get;

class Payment
{

    public static function currencies()
    {
        $currencies = Get::list('currencies');
        $code = Settings::get('currency');
        foreach ($currencies as $currency) {
            if ($currency['code'] == $code) {
                $currency['format'] = static::getFormat($currency);
                return $currency;
            }
        }
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


    public static function methods()
    {
        return apply_filters('wappointment_payment_methods', [
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

        ]);
    }

    public static function isWooActive()
    {
        $methods = static::methods();

        return count($methods) < 2 && $methods[0]['key'] == 'woocommerce';
    }
}
