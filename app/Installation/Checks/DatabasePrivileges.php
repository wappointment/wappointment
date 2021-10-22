<?php

namespace Wappointment\Installation\Checks;

use Wappointment\Services\Settings;

class DatabasePrivileges extends \Wappointment\Installation\MethodsRunner
{
    protected function canConnectToPdo2()
    {
        try {
            \Wappointment\Config\Database::capsule()->getConnection('default')->getPdo();
        } catch (\Throwable $th) {
            \Wappointment\Config\Database::resetCapsule();
            $this->tryAlternativePort();
        }
        return true;
    }

    public function tryAlternativePort()
    {
        try {
            \Wappointment\Config\Database::capsule(true)->getConnection('default')->getPdo();
        } catch (\Throwable $th) {
            throw new \WappointmentException(__('It seems impossible to CONNECT to your Database.', 'wappointment'));
        }
        Settings::save('alt_port', true);
    }
}
