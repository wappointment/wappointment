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
        $foreignName = $this->getFKServices();
        $foreignNameLoc = $this->getFKLocations();

        Capsule::schema()->create(Database::$prefix_self . '_service_location', function ($table) use ($foreignName, $foreignNameLoc) {
            $table->increments('id');
            $table->unsignedInteger('service_id');

            $table->unsignedInteger('location_id');
            $table->foreign('service_id', $foreignName)->references('id')->on(Database::$prefix_self . '_services');
            $table->foreign('location_id', $foreignNameLoc)->references('id')->on(Database::$prefix_self . '_locations');
        });
    }
    protected function getFKServices()
    {
        return $this->getForeignName(Database::$prefix_self . '_service_location_service_id_foreign');
    }
    protected function getFKLocations()
    {
        return $this->getForeignName(Database::$prefix_self . '_service_location_location_id_foreign');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $foreignName = $this->getFKServices();
        $foreignNameLoc = $this->getFKLocations();
        Capsule::schema()->table(Database::$prefix_self . '_service_location', function ($table) use ($foreignName, $foreignNameLoc) {
            $table->dropForeign($foreignName);
            $table->dropForeign($foreignNameLoc);
        });
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_service_location');
    }
}
