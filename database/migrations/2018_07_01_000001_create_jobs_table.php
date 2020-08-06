<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateJobsTable extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Capsule::schema()->hasTable(Database::$prefix_self . '_jobs')) {
            Capsule::schema()->create(Database::$prefix_self . '_jobs', function ($table) {
                $table->bigIncrements('id');
                $table->string('queue')->nullable();
                $table->longText('payload');
                $table->unsignedInteger('appointment_id')->nullable();
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->unsignedInteger('reserved_at')->default(0);
                $table->unsignedInteger('available_at')->default(0);
                $table->unsignedInteger('created_at');
            });
        }


        Capsule::schema()->table(Database::$prefix_self . '_jobs', function ($table) {
            $table->string('queue', 25)->nullable()->change();
            $table->index(['queue', 'reserved_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_jobs');
    }
}
