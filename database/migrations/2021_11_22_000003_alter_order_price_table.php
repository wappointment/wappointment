<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class AlterOrderPriceTable extends Wappointment\Installation\MigrateHasServices
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->table(Database::$prefix_self . '_order_price', function ($table) {
            $table->smallInteger('quantity')->nullable()->default(1);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
