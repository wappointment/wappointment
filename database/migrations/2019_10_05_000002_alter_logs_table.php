<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class AlterLogsTable extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $foreignName = $this->getForeignName(Database::$prefix_self . '_logs' . '_' . 'client_id_foreign');
        Capsule::schema()->table(Database::$prefix_self . '_logs', function ($table) use ($foreignName) {
            $table->unsignedInteger('client_id')->nullable()->default(null)->change();
            $table->foreign('client_id', $foreignName)->references('id')->on(Database::$prefix_self . '_clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $foreignName = $this->getForeignName(Database::$prefix_self . '_logs' . '_' . 'client_id_foreign');
        Capsule::schema()->table(Database::$prefix_self . '_logs', function ($table) use ($foreignName) {
            $table->dropForeign($foreignName);
        });
    }
}
