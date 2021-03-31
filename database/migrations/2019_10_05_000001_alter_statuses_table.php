<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;
use Wappointment\Models\Status;

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
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
