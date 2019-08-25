<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateFailedJobsTable extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create(Database::$prefix_self . '_failed_jobs', function ($table) {
            $table->increments('id');
            $table->text('queue');
            $table->unsignedInteger('appointment_id')->nullable();
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_failed_jobs');
    }
}
