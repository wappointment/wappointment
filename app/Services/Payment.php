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
                return $currency;
            }
        }
    }


    public static function methods()
    {
        return apply_filters('wappointment_payment_methods', [
            [
                'key' => 'onsite',
                'name' => 'On Site',
                'desc' => 'Pay later on site',
                'description' => 'Customers pay you in person at your business\' address or wherever you deliver the service',
                'installed' => true,
                'active' => Settings::get('onsite_active'),

            ],
            [
                'key' => 'stripe',
                'name' => 'Stripe',
                'desc' => 'Pay with credit card',
                'description' => 'Customers pay online with their VISA, Mastercard, Amex etc ... in 44 countries and 135 currencies',
                'installed' => false,
                'hideLabel' => true
            ],
            [
                'key' => 'paypal',
                'name' => 'Paypal',
                'desc' => 'Pay with Paypal',
                'description' => 'Customers pay online with their Paypal Account, VISA, Mastercard, Amex etc ... in 25 currencies and 200 countries',
                'installed' => false,
                'hideLabel' => true
            ],
            [
                'key' => 'woocommerce',
                'name' => 'WooCommerce',
                'desc' => 'Pay with WooCommerce',
                'description' => 'WooCommerce is the most popular ecommerce plugin for WordPress. Already familiar with WooCommerce? Then selling your time with Wappointment and WooCommerce will be real easy.',
                'installed' => false,
                'hideLabel' => true
            ],

        ]);
    }
}
