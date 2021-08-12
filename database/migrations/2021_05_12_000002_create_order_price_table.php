<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateOrderPriceTable extends Wappointment\Installation\Migrate
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $foreignNameOrder = $this->getFKOrders();
        $foreignNamePrice = $this->getFKPrices();
        $foreignNameAppointments = $this->getFKAppointments();
        Capsule::schema()->create(Database::$prefix_self . '_order_price', function ($table) use ($foreignNameOrder, $foreignNamePrice, $foreignNameAppointments) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('price_id');
            $table->unsignedMediumInteger('price_value');
            $table->unsignedInteger('appointment_id')->nullable()->default(null);
            $table->foreign('order_id', $foreignNameOrder)->references('id')->on(Database::$prefix_self . '_orders');
            $table->foreign('price_id', $foreignNamePrice)->references('id')->on(Database::$prefix_self . '_prices');
            $table->foreign('appointment_id', $foreignNameAppointments)->references('id')->on(Database::$prefix_self . '_appointments');
            $table->softDeletes();
            $table->timestamps();
        });
    }


    protected function getFKOrders()
    {
        return $this->getForeignName(Database::$prefix_self . '_order_price_order_id_foreign');
    }

    protected function getFKPrices()
    {
        return $this->getForeignName(Database::$prefix_self . '_order_price_price_id_foreign');
    }

    protected function getFKAppointments()
    {
        return $this->getForeignName(Database::$prefix_self . '_order_price_appointment_id_foreign');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $foreignNameOrder = $this->getFKOrders();
        $foreignNamePrice = $this->getFKPrices();
        $foreignNameAppointments = $this->getFKAppointments();
        Capsule::schema()->table(Database::$prefix_self . '_order_price', function ($table) use ($foreignNameOrder, $foreignNamePrice, $foreignNameAppointments) {
            $table->dropForeign($foreignNameOrder);
            $table->dropForeign($foreignNamePrice);
            $table->dropForeign($foreignNameAppointments);
        });
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_order_price');
    }
}
