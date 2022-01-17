<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreatePricesTable extends Wappointment\Installation\Migrate
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Capsule::schema()->hasTable(Database::$prefix_self . '_prices')) {
            Capsule::schema()->create(Database::$prefix_self . '_prices', function ($table) {
                $table->increments('id');
                $table->string('name');
                $table->unsignedTinyInteger('type')->default(0); //services or pack or more
                $table->unsignedInteger('reference_id'); //refer to a pack or a service
                $table->unsignedInteger('staff_id')->nullable()->default(null);
                $table->unsignedMediumInteger('price')->nullable();
                $table->unsignedInteger('parent')->nullable()->default(null);
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_prices');
    }
}
