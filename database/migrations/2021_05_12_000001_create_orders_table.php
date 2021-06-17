<?php

use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class CreateOrdersTable extends Wappointment\Installation\Migrate
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Capsule::schema()->create(Database::$prefix_self . '_orders', function ($table) {
            $table->increments('id');
            $table->string('transaction_id', 64)->unique()->nullable();
            $table->tinyInteger('status')->default(0);
            $table->unsignedMediumInteger('total')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->tinyInteger('payment')->default(0);
            $table->unsignedInteger('client_id')->nullable()->default(null);
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
        Capsule::schema()->dropIfExists(Database::$prefix_self . '_orders');
    }
}
