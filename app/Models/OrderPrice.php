<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;

class OrderPrice extends Model
{
    protected $table = 'wappo_order_price';

    protected $fillable = [
        'order_id', 'price_id', 'appointment_id', 'price_value', 'item_name',
    ];
    protected $with = ['price'];

    public function price()
    {
        return $this->belongsTo(Price::class, 'price_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }
}
