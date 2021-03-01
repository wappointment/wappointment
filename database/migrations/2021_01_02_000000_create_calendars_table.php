<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateCalendarsTable extends Wappointment\Installation\MigrateHasServices
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
        Capsule::schema()->create(Database::$prefix_self . '_calendars', function ($table) {
            $table->increments('id');
            $table->unsignedBigInteger('wp_uid')->default(0);
            $table->unsignedTinyInteger('sorting')->default(0);
            $table->mediumText('options')->nullable();
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
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_calendars');
    }
}
