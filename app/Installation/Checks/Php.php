<?php

namespace Wappointment\Installation\Checks;

class Php extends \Wappointment\Installation\MethodsRunner
{

    protected function canRunPhp()
    {

        if (version_compare(PHP_VERSION, WAPPOINTMENT_PHP_MIN) < 0) {
            throw new \WappointmentException(
                'Your website\'s PHP version(' . PHP_VERSION . ') is lower to our minimum requirement '
                    . WAPPOINTMENT_PHP_MIN
            );
        }
        $max = '8.0.0';
        if (version_compare(PHP_VERSION, $max) >= 0) {
            throw new \WappointmentException(
                'Wappointment is not yet compatible with PHP 8 yet. You can install our PHP 8 beta version following this guide: https://wappointment.com/docs/installing-php8-version/'
            );
        }
    }
}
