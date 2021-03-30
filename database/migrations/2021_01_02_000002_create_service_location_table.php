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
        $foreignName = $this->getForeignName(Database::$prefix_self . '_service_location_service_id_foreign');
        $foreignNameLoc = $this->getForeignName(Database::$prefix_self . '_service_location_location_id_foreign');

        Capsule::schema()->create(Database::$prefix_self . '_service_location', function ($table) use ($foreignName, $foreignNameLoc) {
            $table->increments('id');
            $table->unsignedInteger('service_id');

            $table->unsignedInteger('location_id');
            if ($foreignName === false) {
                $table->foreign('service_id')->references('id')->on(Database::$prefix_self . '_services');
            } else {
                $table->foreign('service_id', $foreignName)->references('id')->on(Database::$prefix_self . '_services');
            }
            if ($foreignNameLoc === false) {
                $table->foreign('location_id')->references('id')->on(Database::$prefix_self . '_locations');
            } else {
                $table->foreign('location_id', $foreignNameLoc)->references('id')->on(Database::$prefix_self . '_locations');
            }
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
