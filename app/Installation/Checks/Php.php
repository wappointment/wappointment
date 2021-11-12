<?php

namespace Wappointment\Installation\Checks;

class Php extends \Wappointment\Installation\MethodsRunner
{

    protected function canUseMbString()
    {

        if (extension_loaded('mbstring')) {
            return true;
        } else {
            throw new \WappointmentException('Wappointment requires the PHP module "mbstring" to work.');
        }
    }

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
                'That\'s embarassing... Wappointment is not compatible with PHP 8 yet, we\'re working on it. You can download our PHP 8 beta version here: http://wappointment.com/uploads/wappointment-php8.zip'
            );
        }
    }
}
