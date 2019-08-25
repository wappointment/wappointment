<?php

namespace Wappointment\Installation\Steps;


class CreateTables extends \Wappointment\Installation\MethodsRunner
{
    protected function canRun()
    {
        // 1 - migrate tables
        $migrate = new \Wappointment\Installation\Migrate();

        $migrate->migrate();
    }
}
