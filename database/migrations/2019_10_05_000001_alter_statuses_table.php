<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;
use Wappointment\Models\Status;
use Wappointment\System\Status as SystemStatus;

class AlterStatusesTable extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Status::where('staff_id', 0)->update(['staff_id' => null]);
        Capsule::schema()->table(Database::$prefix_self . '_statuses', function ($table) {
            $table->unsignedInteger('staff_id')->nullable()->default(null)->change();
            $table->foreign('staff_id')->references('id')->on(Database::$prefix_self . '_clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->table(Database::$prefix_self . '_statuses', function ($table) {
            $table->dropForeign(['staff_id']);
        });
    }
}
