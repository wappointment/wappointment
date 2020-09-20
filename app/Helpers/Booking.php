<?php

namespace Wappointment\Helpers;

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
}
