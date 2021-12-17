<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;
use Wappointment\System\Status;

class CreateAppointmentsTable extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create(Database::$prefix_self . '_appointments', function ($table) {
            $table->increments('id');
            $table->datetime('start_at');
            $table->datetime('end_at');
            $table->unsignedTinyInteger('type')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->string('edit_key', 32)->nullable();
            $table->timestamps();
            $table->text('options')->nullable();
            $table->unsignedInteger('staff_id')->nullable()->default(null);
            $table->unsignedInteger('service_id')->nullable()->default(null);
            $table->unsignedInteger('client_id')->nullable()->default(null);
            $table->unique(['staff_id', 'status', 'start_at', 'end_at'], 'unique_staff_id_status_start_end');
        });
        Status::dbVersionOnCreation();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_appointments');
    }
}
