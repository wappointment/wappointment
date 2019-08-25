<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateStatusesTable extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create(Database::$prefix_self . '_statuses', function ($table) {
            $table->increments('id');
            $table->unsignedTinyInteger('type')->default(0);
            $table->datetime('start_at');
            $table->datetime('end_at');
            $table->unsignedTinyInteger('recur')->default(0);
            $table->unsignedTinyInteger('muted')->default(0);
            $table->string('source', 32)->nullable();
            $table->string('eventkey', 32)->nullable();
            $table->mediumText('options')->nullable();
            $table->unsignedTinyInteger('staff_id')->default(0);
            $table->unique(['staff_id', 'type', 'start_at', 'end_at', 'source', 'eventkey'], 'unique_staff_id_type_start_end_source_eventkey');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_statuses');
    }
}
