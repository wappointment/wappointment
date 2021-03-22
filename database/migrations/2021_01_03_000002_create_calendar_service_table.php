<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateCalendarServiceTable extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create(Database::$prefix_self . '_calendar_service', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->foreign('service_id')->references('id')->on(Database::$prefix_self . '_services');
            $table->unsignedInteger('calendar_id');
            $table->foreign('calendar_id')->references('id')->on(Database::$prefix_self . '_calendars');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->table(Database::$prefix_self . '_calendar_service', function ($table) {
            $table->dropForeign(['service_id']);
            $table->dropForeign(['calendar_id']);
        });
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_calendar_service');
    }
}
