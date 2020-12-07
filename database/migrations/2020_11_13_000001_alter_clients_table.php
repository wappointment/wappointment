<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;


class AlterClientsTable extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Capsule::schema()->table(Database::$prefix_self . '_clients', function ($table) {
            $table->softDeletes();
        });
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
