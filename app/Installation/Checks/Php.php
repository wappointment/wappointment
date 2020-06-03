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
    }
}
