<?php

namespace Wappointment\Services;

use Wappointment\Models\Price as ModelsPrice;
use Wappointment\Services\Price;

class AdminPackage
{
    public static function dataSave($packData, $pack_options_db)
    {

        foreach ($packData['options']['variations'] as $key => $variationObj) {
            $variationDB = $pack_options_db['variations'][$key];

            $price_id = !empty($variationDB['price_id']) ? $variationDB['price_id'] : false;
            $price = !empty($variationObj['price']) ? $variationObj['price'] : '';

            if (!empty($packData['options']['variations'][$key]['delete'])) {
                unset($pack_options_db['variations'][$key]);
                unset($packData['options']['variations'][$key]);
                if (!empty($price_id)) {
                    static::deletePrice($price_id);
                }
            } else {
                if (!empty($price) && !empty($packData['id'])) {
                    $pack_options_db['variations'][$key]['price_id'] = static::recordPrice($packData['id'], $price, $price_id, 'Credits : ' . $variationObj['credits']);
                }
            }
        }
        $packData['options']['variations'] = array_values($pack_options_db['variations']);
        return $packData;
    }

    public static function recordPrice($package_id, $price_value, $price_id, $details)
    {
        $price = new Price(empty($price_id) ? false : $price_id);
        $price->forPackage();
        $price->setPrice($price_value);
        $price->setData([
            'reference_id' => !empty($package_id) ? $package_id : 0,
            'name' => $details,
        ]);
        return $price->save();
    }

    public static function deletePrice($price_id)
    {
        ModelsPrice::where('id', $price_id)->delete();
    }


    public static function delete($old_package)
    {

        foreach ($old_package->options['variations'] as $variationObj) {
            if (!empty($variationObj['price_id'])) {
                static::deletePrice($variationObj['price_id']);
            }
        }
        return $old_package;
    }
}
