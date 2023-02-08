<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class AlterRemindersTableForMultilang extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->table(Database::$prefix_self . '_reminders', function ($table) {
            $table->string('lang',6)->nullable()->default(null);
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
