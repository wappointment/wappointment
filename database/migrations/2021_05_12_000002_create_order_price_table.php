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

        //this is for people who had an issue running the initial migrations with long foreign key names
        if (Capsule::schema()->hasTable(Database::$prefix_self . '_order_price')) {
            $this->down(false);
        }
        Capsule::schema()->create(Database::$prefix_self . '_order_price', function ($table) use ($foreignNameOrder, $foreignNamePrice, $foreignNameAppointments) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('price_id');
            $table->string('item_name');
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
        return $this->getForeignName(Database::$prefix_self . '_orderp_oid_foreign');
    }

    protected function getFKPrices()
    {
        return $this->getForeignName(Database::$prefix_self . '_orderp_pid_foreign');
    }

    protected function getFKAppointments()
    {
        return $this->getForeignName(Database::$prefix_self . '_orderp_aid_foreign');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down($fk = true)
    {
        $foreignNameOrder = $this->getFKOrders();
        $foreignNamePrice = $this->getFKPrices();
        $foreignNameAppointments = $this->getFKAppointments();
        if ($fk) {
            Capsule::schema()->table(Database::$prefix_self . '_order_price', function ($table) use ($foreignNameOrder, $foreignNamePrice, $foreignNameAppointments) {
                $table->dropForeign($foreignNameOrder);
                $table->dropForeign($foreignNamePrice);
                $table->dropForeign($foreignNameAppointments);
            });
        }

        Capsule::schema()->dropIfExists(Database::$prefix_self . '_order_price');
    }
}
