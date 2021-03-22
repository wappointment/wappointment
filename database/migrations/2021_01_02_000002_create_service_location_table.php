<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateServiceLocationTable extends Wappointment\Installation\MigrateHasServices
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($this->hasMultiService()) {
            return;
        }
        Capsule::schema()->create(Database::$prefix_self . '_service_location', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->foreign('service_id')->references('id')->on(Database::$prefix_self . '_services');
            $table->unsignedInteger('location_id');
            $table->foreign('location_id')->references('id')->on(Database::$prefix_self . '_locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->table(Database::$prefix_self . '_service_location', function ($table) {
            $table->dropForeign(['service_id']);
            $table->dropForeign(['location_id']);
        });
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_service_location');
    }
}
