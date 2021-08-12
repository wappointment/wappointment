<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\ClassConnect\ClientSoftDeletes as SoftDeletes;
use Wappointment\Services\AppointmentNew;
use Wappointment\Services\Payment;

class Order extends Model
{
    use SoftDeletes;

    protected $table = 'wappo_orders';
    protected $fillable = ['transaction_id', 'status', 'total', 'refunded_at', 'client_id', 'options', 'paid_at', 'payment', 'currency'];
    protected $with = ['client', 'prices', 'appointments'];
    protected $dates = ['refunded_at', 'paid_at', 'created_at', 'updated_at'];
    protected $casts = ['options' => 'array'];
    protected $appends = ['charge', 'payment_label', 'status_label'];

    const STATUS_PENDING = 0;
    const STATUS_AWAITING = 1;
    const STATUS_PAID = 2;
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
                return __('Pending', 'wappointment');
            case self::STATUS_AWAITING:
                return  __('Awaiting payment', 'wappointment');
            case self::STATUS_PAID:
                return  __('Paid', 'wappointment');
            case self::STATUS_CANCELLED:
                return  __('Cancelled', 'wappointment');
            case self::STATUS_REFUNDED:
                return  __('Refunded', 'wappointment');
        }
    }

    public function getPaymentLabelAttribute()
    {
        switch ($this->payment) {
            case self::PAYMENT_ONSITE:
                return __('Pay On Site', 'wappointment');
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

    public function setPaid()
    {
        $this->currency = Payment::currencyCode();
        $this->status = static::STATUS_PAID;
        $this->paid_at = date('Y-m-d H:i:s');
    }
    public function setPaypal()
    {
        $this->payment = static::PAYMENT_PAYPAL;
    }
    public function setStripe()
    {
        $this->payment = static::PAYMENT_STRIPE;
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
            $this->recordItem($price->id, $price->price, $appointment->id);
        }
    }

    public function recordItem($price_id, $price_value, $appointment_id)
    {
        OrderPrice::create([
            'order_id' => $this->id,
            'price_id' => $price_id,
            'price_value' => $price_value,
            'appointment_id' => $appointment_id,
        ]);
    }

    public function refreshTotal()
    {
        $prices = OrderPrice::where('order_id', $this->id)->with('price')->get();
        $total = 0;
        foreach ($prices as $price) {
            $total += $price->price_value;
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

        $this->setPaid();

        if ($save) {
            $this->save();
        }

        return $this;
    }
}
