<?php

namespace Wappointment\Installation\Checks;

class Database extends \Wappointment\Installation\MethodsRunner
{

    protected function canConnectToPdo0()
    {

        if (extension_loaded('pdo')) {
            return true;
        } else {
            throw new \WappointmentException('Wappointment requires the PHP module "pdo" to work.');
        }
    }

    protected function canConnectToPdo1()
    {

        if (extension_loaded('pdo_mysql')) {
            return true;
        } else {
            throw new \WappointmentException('Wappointment requires the PHP module "pdo_mysql" to work.');
        }
    }
}
