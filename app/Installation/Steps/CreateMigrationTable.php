<?php

namespace Wappointment\Installation\Steps;

use Wappointment\Installation\Migrate;

class CreateMigrationTable extends \Wappointment\Installation\MethodsRunner
{
    protected function canRun()
    {
        throw new \Exception("Error Processing Request", 1);

        //setup migration table
        new Migrate();
    }
}
