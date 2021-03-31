<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateCalendarServiceTable extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $foreignName = $this->getFKServices();
        $foreignNameLoc = $this->getFKCalendars();

        Capsule::schema()->create(Database::$prefix_self . '_calendar_service', function ($table) use ($foreignName, $foreignNameLoc) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('calendar_id');
            if ($foreignName === false) {
                $table->foreign('service_id')->references('id')->on(Database::$prefix_self . '_services');
            } else {
                $table->foreign('service_id', $foreignName)->references('id')->on(Database::$prefix_self . '_services');
            }
            if ($foreignNameLoc === false) {
                $table->foreign('calendar_id')->references('id')->on(Database::$prefix_self . '_calendars');
            } else {
                $table->foreign('calendar_id', $foreignNameLoc)->references('id')->on(Database::$prefix_self . '_calendars');
            }
        });
    }

    protected function getFKServices()
    {
        return $this->getForeignName(Database::$prefix_self . '_calendar_service_service_id_foreign');
    }
    protected function getFKCalendars()
    {
        return $this->getForeignName(Database::$prefix_self . '_calendar_service_calendar_id_foreign');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $foreignName = $this->getFKServices();
        $foreignNameLoc = $this->getFKCalendars();
        Capsule::schema()->table(Database::$prefix_self . '_calendar_service', function ($table) use ($foreignName, $foreignNameLoc) {
            if ($foreignName === false) {
                $table->dropForeign(['service_id']);
            } else {
                $table->dropForeign($foreignName);
            }
            if ($foreignNameLoc === false) {
                $table->dropForeign(['calendar_id']);
            } else {
                $table->dropForeign($foreignNameLoc);
            }
        });
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_calendar_service');
    }
}
