<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateLogsTable extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create(Database::$prefix_self . '_logs', function ($table) {
            $table->increments('id');
            $table->unsignedTinyInteger('type')->default(0);
            $table->timestamps();
            $table->text('options')->nullable();
            $table->unsignedInteger('client_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_logs');
    }
}
