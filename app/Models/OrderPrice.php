<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\ClassConnect\ClientSoftDeletes as SoftDeletes;

class OrderPrice extends Model
{
    use SoftDeletes;

    protected $table = 'wappo_order_price';

    protected $fillable = [
        'order_id', 'price_id', 'appointment_id',
    ];
    protected $with = ['price'];

    public function price()
    {
        return $this->belongsTo(Price::class, 'price_id');
    }
}
