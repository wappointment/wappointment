<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;
use Wappointment\Models\Status;

class AlterLogsTable extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->table(Database::$prefix_self . '_logs', function ($table) {
            $table->unsignedInteger('client_id')->nullable()->default(null)->change();
            $table->foreign('client_id')->references('id')->on(Database::$prefix_self . '_clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->table(Database::$prefix_self . '_logs', function ($table) {
            $table->dropForeign(['client_id']);
        });
    }
}
