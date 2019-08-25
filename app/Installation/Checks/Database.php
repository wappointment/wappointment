<?php

namespace Wappointment\Installation\Checks;

class Database extends \Wappointment\Installation\MethodsRunner
{
    protected function canConnectToPdo()
    {

        try {
            \Wappointment\Config\Database::capsule()->getConnection('default')->getPdo();
        } catch (\Throwable $th) {
            throw new \WappointmentException('It seems impossible to CONNECT to your Database');
        }

        return true;
    }

    protected function canInstallAndRun()
    {
        //$testPrivileges = ['CREATE', 'DELETE', 'INSERT', 'ALTER', 'UPDATE'];
        $testPrivileges = ['CREATE', 'DELETE', 'INSERT', 'UPDATE'];
        try {
            $db = \Wappointment\Config\Database::capsule()->getConnection('default');
            $result = $db->select('SHOW GRANTS;');

            foreach ($result[0] as $res) {
                if (strpos($res, 'GRANT ') !== false) {
                    if (strpos($res, 'ALL PRIVILEGES') !== false) {
                        return true;
                    }

                    $cannot_do = [];
                    foreach ($testPrivileges as $privilege) {
                        if (strpos($res, $privilege) === false) {
                            $cannot_do[] = $privilege;
                        }
                    }
                }
            }

            if (!empty($cannot_do)) {
                $db_config = new \Wappointment\WP\Database();
                throw new \WappointmentException('It seems your SQL user "' . $db_config->getDbUser() . '" is missing privileges ' . implode(', ', $cannot_do) . ' to your database "' . $db_config->getDbName() . '"');
            }
        } catch (\Throwable $th) {
            throw  $th;
            throw new \WappointmentException('It seems impossible to CREATE tables on your database');
        }

        return true;
    }
}
