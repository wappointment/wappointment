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

        Capsule::schema()->table(Database::$prefix_self . '_statuses', function ($table) {
            $table->unsignedInteger('staff_id')->nullable()->default(null)->change();
        });

        Status::where('staff_id', 0)->update(['staff_id' => null]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    { }
}
