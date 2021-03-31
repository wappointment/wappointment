<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;
use Wappointment\System\Status;

class AlterLogsTable extends Wappointment\Installation\Migrate
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $foreignName = $this->getFKClients();
        Capsule::schema()->table(Database::$prefix_self . '_logs', function ($table) use ($foreignName) {
            $table->unsignedInteger('client_id')->nullable()->default(null)->change();
            if ($foreignName === false) {
                $table->foreign('client_id')->references('id')->on(Database::$prefix_self . '_clients');
            } else {
                $table->foreign('client_id', $foreignName)->references('id')->on(Database::$prefix_self . '_clients');
            }
        });
    }

    protected function getFKClients()
    {
        return $this->getForeignName(Database::$prefix_self . '_logs' . '_' . 'client_id_foreign');
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $foreignName = $this->getFKClients();
        Capsule::schema()->table(Database::$prefix_self . '_logs', function ($table) use ($foreignName) {
            if ($foreignName === false) {
                $table->dropForeign(['client_id']);
            } else {
                $table->dropForeign($foreignName);
            }
        });
    }
}
