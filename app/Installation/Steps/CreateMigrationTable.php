<?php

namespace Wappointment\Installation\Steps;

use Wappointment\Installation\Migrate;

class CreateMigrationTable extends \Wappointment\Installation\MethodsRunner
{
    protected function canRun()
    {

        //setup migration table
        new Migrate();
    }
}
