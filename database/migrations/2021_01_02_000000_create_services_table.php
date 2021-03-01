<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateServicesTable extends Wappointment\Installation\MigrateHasServices
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if ($this->hasMultiService()) {
            return;
        }
        Capsule::schema()->create(Database::$prefix_self . '_services', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedTinyInteger('sorting')->default(0);
            $table->text('options')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_services');
    }
}
