<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class AlterAppointmentsTableForRecurrence extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->table(Database::$prefix_self . '_appointments', function ($table) {
            $table->unsignedTinyInteger('recurrent')->default(0);
            $table->unsignedInteger('parent')->nullable()->default(null);
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
