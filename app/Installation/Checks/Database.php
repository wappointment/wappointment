<?php

namespace Wappointment\Installation\Checks;

class Database extends \Wappointment\Installation\MethodsRunner
{

    protected function canConnectToPdo0()
    {

        if (extension_loaded('pdo')) {
            return true;
        } else {
            throw new \WappointmentException(__('Wappointment requires the php extension "pdo" to work.', 'wappointment'));
        }
    }

    protected function canConnectToPdo1()
    {

        if (extension_loaded('pdo_mysql')) {
            return true;
        } else {
            throw new \WappointmentException(__('Wappointment requires the php extension "pdo_mysql" to work.', 'wappointment'));
        }
    }
}
