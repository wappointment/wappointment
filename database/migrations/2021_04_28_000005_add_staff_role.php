<?php

use Wappointment\Services\Permissions;

class AddStaffRole extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $perms = new Permissions;
        $perms->registerRole('wappointment_staff');
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
