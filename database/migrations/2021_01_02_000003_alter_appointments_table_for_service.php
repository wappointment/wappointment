<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;
use Wappointment\Models\Appointment;

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
        Capsule::schema()->table(Database::$prefix_self . '_appointments', function ($table) {
            $table->foreign('service_id')->references('id')->on(Database::$prefix_self . '_services');
            $table->unsignedInteger('location_id')->nullable()->default(null);
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

        Capsule::schema()->table(Database::$prefix_self . '_appointments', function ($table) {
            Appointment::whereNotNull('service_id')->update(['service_id' => null]);
            $table->dropForeign(['service_id']);
            $table->dropForeign(['location_id']);
            $table->dropColumn(['location_id']);
        });
    }
}
