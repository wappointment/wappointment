<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

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
            $table->mediumText('options')->nullable();
            $table->integer('client_id')->foreign()->references('id')->on(Database::$prefix_self . '_clients');
            $table->unsignedTinyInteger('staff_id')->default(0);
            $table->unsignedTinyInteger('service_id')->default(0);
            $table->unique(['staff_id', 'status', 'start_at', 'end_at'], 'unique_staff_id_status_start_end');
        });
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
