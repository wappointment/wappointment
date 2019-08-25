<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateRemindersTable extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create(Database::$prefix_self . '_reminders', function ($table) {
            $table->increments('id');
            $table->string('subject')->nullable();
            $table->tinyInteger('type')->default(1);
            $table->tinyInteger('event')->default(1);
            $table->tinyInteger('locked')->default(0);
            $table->tinyInteger('published')->default(0);
            $table->mediumText('options')->nullable();
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
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_reminders');
    }
}
