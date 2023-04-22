<?php

namespace Wappointment\Services;
use Wappointment\ClassConnect\Capsule;
use Wappointment\Config\Database;

class BigPrice 
{
    public function setup()
    {
        try {
            Capsule::schema()->table(Database::$prefix_self . '_prices', function ($table) {
                $table->unsignedBigInteger('price')->change();
            });
    
            Capsule::schema()->table(Database::$prefix_self . '_order_price', function ($table) {
                $table->unsignedBigInteger('price_value')->change();
            });
        } catch (\Throwable $th) {
            throw $th;
        }

        Settings::save('big_prices',true);
    }
}
