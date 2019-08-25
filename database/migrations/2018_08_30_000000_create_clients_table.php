<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateClientsTable extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create(Database::$prefix_self . '_clients', function ($table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('name');
            $table->mediumText('options')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_clients');
    }
}
