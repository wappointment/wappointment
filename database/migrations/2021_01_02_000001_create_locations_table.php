<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateLocationsTable extends Wappointment\Installation\MigrateHasServices
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
        Capsule::schema()->create(Database::$prefix_self . '_locations', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedTinyInteger('type')->default(1);
            $table->tinyInteger('status')->default(0);
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
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_locations');
    }
}
