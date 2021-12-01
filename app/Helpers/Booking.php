<?php

namespace Wappointment\Helpers;

use Wappointment\Models\Service as ModelsService;

class Booking
{
    public $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : '';
    }

    public function input($key)
    {
        return $this->get($key);
    }

    public function getService()
    {
        return ModelsService::find($this->data['service']);
    }
}
