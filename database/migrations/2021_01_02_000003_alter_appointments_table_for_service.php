<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;
use Wappointment\Models\Appointment;
use Wappointment\System\Status;
use Wappointment\Services\Services;

class AlterAppointmentsTableForService extends Wappointment\Installation\MigrateHasServices
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($this->hasMultiService() && count(Services::all()) > 0) {
            return;
        }
        $foreignName = $this->getFKServices();
        $foreignNameLoc = $this->getFKLocations();

        Capsule::schema()->table(Database::$prefix_self . '_appointments', function ($table) use ($foreignName, $foreignNameLoc) {
            $table->unsignedInteger('location_id')->nullable()->default(null);
            $table->foreign('service_id', $foreignName)->references('id')->on(Database::$prefix_self . '_services');
            $table->foreign('location_id', $foreignNameLoc)->references('id')->on(Database::$prefix_self . '_locations');
        });
    }

    protected function getFKServices()
    {
        return $this->getForeignName(Database::$prefix_self . '_appointments_service_id_foreign');
    }
    protected function getFKLocations()
    {
        return $this->getForeignName(Database::$prefix_self . '_appointments_location_id_foreign');
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
        Capsule::schema()->table(Database::$prefix_self . '_appointments', function ($table) use ($foreignName, $foreignNameLoc) {
            Appointment::whereNotNull('service_id')->update(['service_id' => null]);

            $table->dropForeign($foreignName);
            $table->dropForeign($foreignNameLoc);
        });
    }
}
