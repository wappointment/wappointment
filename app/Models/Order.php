<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\ClassConnect\ClientSoftDeletes as SoftDeletes;
use Wappointment\Services\AppointmentNew;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'wappo_orders';
    protected $fillable = ['transaction_id', 'status', 'total', 'refunded_at', 'client_id', 'options', 'paid_at', 'payment'];
    protected $with = ['client', 'prices', 'appointments'];
    protected $dates = ['refunded_at', 'paid_at', 'created_at', 'updated_at'];
    protected $casts = ['options' => 'array'];
    protected $appends = ['charge', 'payment_label', 'status_label'];

    const STATUS_PENDING = 0;
    const STATUS_AWAITING = 1;
    const STATUS_COMPLETED = 2;
    const STATUS_CANCELLED = -1;
    const STATUS_REFUNDED = -2;

    const PAYMENT_ONSITE = 0;
    const PAYMENT_STRIPE = 1;
    const PAYMENT_PAYPAL = 2;


    public function appointments()
    {
        return $this->belongsToMany(
            Appointment::class,
            'wappo_order_price'
        );
    }

    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'Pending';
            case self::STATUS_AWAITING:
                return  'Awaiting payment';
            case self::STATUS_COMPLETED:
                return  'Completed';
            case self::STATUS_CANCELLED:
                return  'Cancelled';
            case self::STATUS_REFUNDED:
                return  'Refunded';
        }
    }

    public function getPaymentLabelAttribute()
    {
        switch ($this->payment) {
            case self::PAYMENT_ONSITE:
                return 'Pay On Site';
            case self::PAYMENT_STRIPE:
                return  'Stripe';
            case self::PAYMENT_PAYPAL:
                return  'Paypal';
        }
    }


    public function isOnSite()
    {
        return $this->payment == self::PAYMENT_ONSITE;
    }

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
        $this->status = static::STATUS_AWAITING;
        if ($this->isOnSite()) {
            $options = $this->client->options;
            if (empty($this->client->options['owes'])) {
                $options['owes'] = 0;
            }
            //set client owing
            $options['owes'] += $this->total;
            $this->client->options = $options;
            $this->client->save();
        }
    }

    public function setCompleted()
    {
        $this->status = static::STATUS_COMPLETED;
        $this->paid_at = date('Y-m-d H:i:s');
    }
    public function setPaypal()
    {
        $this->payment = static::PAYMENT_PAYPAL;
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

    public function complete($save = true)
    {
        $this->confirmAppointments();

        $this->setCompleted();

        if ($save) {
            $this->save();
        }

        return $this;
    }
}
