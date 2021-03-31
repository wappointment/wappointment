<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;
use Wappointment\Models\Appointment;
use Wappointment\System\Status;

class AlterAppointmentsTableForService extends Wappointment\Installation\MigrateHasServices
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

        Capsule::schema()->table(Database::$prefix_self . '_appointments', function ($table) use ($foreignName, $foreignNameLoc) {
            $table->unsignedInteger('location_id')->nullable()->default(null);
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

            if ($foreignName === false) {
                $table->dropForeign(['service_id']);
            } else {
                $table->dropForeign($foreignName);
            }
            if ($foreignNameLoc === false) {
                $table->dropForeign(['location_id']);
            } else {
                $table->dropForeign($foreignNameLoc);
            }

            $table->dropColumn(['location_id']);
        });
    }
}
