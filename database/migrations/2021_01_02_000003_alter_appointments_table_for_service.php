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
        $foreignName = $this->getForeignName(Database::$prefix_self . '_appointments_service_id_foreign');
        $foreignNameLoc = $this->getForeignName(Database::$prefix_self . '_appointments_location_id_foreign');

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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Capsule::schema()->table(Database::$prefix_self . '_appointments', function ($table) {
            Appointment::whereNotNull('service_id')->update(['service_id' => null]);
            $table->dropForeign(['service_id']);
            $table->dropForeign(['location_id']);
            $table->dropColumn(['location_id']);
        });
    }
}
