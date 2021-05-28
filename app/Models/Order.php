<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\ClassConnect\ClientSoftDeletes as SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'wappo_orders';

    protected $fillable = [
        'transaction_id', 'status', 'total', 'refunded_at', 'client_id'
    ];
    protected $with = ['prices'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 0);
    }

    public function prices()
    {
        return $this->hasMany('Wappointment\Models\OrderPrice', 'order_id');
    }

    public function add(Appointment $appointment)
    {
        $prices = $appointment->getServicesPrices();

        foreach ($prices as $price) { //TODO insert many rows at once
            OrderPrice::create([
                'order_id' => $this->id,
                'price_id' => $price->id,
                'appointment_id' => $appointment->id,
            ]);
        }
    }

    public function refreshTotal()
    {
        $prices = OrderPrice::where('order_id', $this->id)->with('price')->get();
        $total = 0;
        foreach ($prices as $price) {
            $total += $price->price->price;
        }
        $this->update(['total' => $total]);
    }
}
