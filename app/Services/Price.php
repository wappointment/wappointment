<?php

namespace Wappointment\Services;

use Wappointment\Models\Price as ModelsPrice;

class Price
{
    public $id;
    public $type;
    public $price;
    public $data;

    public function __construct($id = false)
    {
        $this->id = $id;
    }

    public function forService()
    {
        $this->type = ModelsPrice::TYPE_SERVICE;
    }

    public function forPackage()
    {
        $this->type = ModelsPrice::TYPE_PACKAGE;
    }

    // we convert into an int
    public function setPrice($price)
    {
        $this->price = strpos($price, '.') !== false ? str_replace('.', '', $price) : (int)$price * 100;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return array_merge($this->data, [
            'type' => $this->type,
            'price' => (int)$this->price
        ]);
    }

    public function save()
    {
        if ($this->id === false) {
            return ModelsPrice::create($this->getData());
        } else {
            return ModelsPrice::where('id', (int)$this->id)->update($this->getData());
        }
    }
}
