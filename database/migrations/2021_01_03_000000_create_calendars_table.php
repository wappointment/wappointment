<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateCalendarsTable extends Wappointment\Installation\Migrate
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Capsule::schema()->create(Database::$prefix_self . '_calendars', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('account_key')->nullable();
            $table->unsignedBigInteger('wp_uid')->default(0);
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedTinyInteger('sorting')->default(0);
            $table->text('options')->nullable();
            $table->mediumText('availability')->nullable();
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
