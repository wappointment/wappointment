<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\ClassConnect\ClientSoftDeletes as SoftDeletes;
use Wappointment\Services\AppointmentNew;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'wappo_orders';
    protected $fillable = ['transaction_id', 'status', 'total', 'refunded_at', 'client_id'];
    protected $with = ['prices'];
    protected $appends = ['charge'];

    const STATUS_PENDING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_CANCELLED = -1;
    const STATUS_REFUNDED = -2;

    public function getChargeAttribute()
    {
        return $this->total;
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 0);
    }

    public function setProcessing()
    {
        $this->status = static::STATUS_PROCESSING;
    }

    public function setCompleted()
    {
        $this->status = static::STATUS_COMPLETED;
    }

    public function setCancelled()
    {
        $this->status = static::STATUS_CANCELLED;
    }

    public function setRefund()
    {
        $this->status = static::STATUS_REFUNDED;
    }

    public function prices()
    {
        return $this->hasMany('Wappointment\Models\OrderPrice', 'order_id');
    }

    public function clearLastAdded()
    {
        $appointment_ids = [];
        $charge_ids = [];
        foreach ($this->prices as $charge) {
            $appointment_ids[] = $charge->appointment_id;
            $charge_ids[] = $charge->id;
        }

        AppointmentNew::silentCancel($appointment_ids, $charge_ids);
    }

    public function add(Appointment $appointment)
    {

        //clear all prices by cancelling previously placed appointment silently
        $this->clearLastAdded();
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

    public function confirmAppointments()
    {
        foreach ($this->prices as $charge) {
            AppointmentNew::confirm($charge->appointment_id);
        }
    }

    public function complete()
    {
        $this->confirmAppointments();

        $this->setCompleted();
        $this->save();
        return $this;
    }
}
