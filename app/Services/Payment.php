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
                'desc' => 'Pay later on site'
            ],
            // [
            //     'key' => 'stripe',
            //     'cards' => ['visa', 'mastercard', 'amex'],
            //     'desc' => 'Pay securely with Stripe',
            // ],
            // [
            //     'key' => 'paypal',
            //     'cards' => ['visa', 'mastercard', 'amex'],
            //     'desc' => 'Pay securely with Paypal',
            // ],
        ]);
    }

    // [
    //     [
    //         'key' => 'stripe',
    //         'cards' => ['visa', 'mastercard', 'amex'],
    //         'desc' => 'Pay securely with Stripe',
    //     ],
    //     [
    //         'key' => 'paypal',
    //         'cards' => ['visa', 'mastercard', 'amex'],
    //         'desc' => 'Pay securely with Paypal',
    //     ],

    // ]
}
