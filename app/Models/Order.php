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
    protected $fillable = ['transaction_id', 'status', 'total', 'refunded_at', 'client_id', 'options', 'paid_at', 'payment', 'currency', 'tax_percent', 'tax_amount'];
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
    public function isStripe()
    {
        return $this->payment == self::PAYMENT_STRIPE;
    }
    public function isPaypal()
    {
        return $this->payment == self::PAYMENT_PAYPAL;
    }

    public function getChargeAttribute()
    {
        return $this->total + $this->tax_amount;
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

        do_action('wappointment_order_cancelled', $this);
        $this->cancelAllConnected();
        $this->decrementOwes();
    }

    /*
    cancel all products related to that order
    */
    private function cancelAllConnected()
    {

        foreach ($this->prices as $charge) {
            //cancel appointment
            if ($charge->appointment_id > 0) {
                AppointmentNew::cancel($charge->appointment, $this->client);
            }
        }
    }

    public function setAutoCancelled()
    {
        $this->status = static::STATUS_CANCELLED;
        $options = $this->options;
        $options['auto_cancelled_at'] = time();
        $this->options = $options;
        $this->save();
    }

    public function setRefund()
    {
        $this->status = static::STATUS_REFUNDED;
        $this->refunded_at = date('Y-m-d H:i:s');
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

    public function add(TicketAbstract $ticket, $slots = false)
    {

        //clear all prices by cancelling previously placed appointment silently
        $this->clearLastAdded();

        if ($ticket->paidWithPackage()) {
            $prices = $ticket->getPackagePrices();
        } else {
            $prices = $ticket->getServicesPrices();
        }

        foreach ($prices as $price) {
            $this->recordItem($price->id, $price->price, $ticket->getAppointment()->id, $price->generateItemName($ticket), $slots);
        }
    }

    public function getDescription()
    {
        $description = '';
        foreach ($this->prices as $price) {
            $description .= "\n" . $price->item_name;
        }
        return $description;
    }

    public function recordItem($price_id, $price_value, $appointment_id, $item_name, $quantity = false)
    {
        OrderPrice::create([
            'order_id' => $this->id,
            'price_id' => $price_id,
            'item_name' => $item_name,
            'price_value' => $price_value,
            'appointment_id' => $appointment_id,
            'quantity' => $quantity === false ? 1 : $quantity,
        ]);
    }

    public function refreshTotal()
    {
        $prices = OrderPrice::where('order_id', $this->id)->with('price')->get();
        $total = 0;
        foreach ($prices as $price) {
            $total += $price->price_value * ($this->getOrderPriceQuantity($price));
        }
        $this->update(
            [
                'total' => $total,
                'tax_amount' => $this->calculateTax($total)
            ]
        );
    }

    public function getOrderPriceQuantity($orderPrice)
    {
        return !empty($orderPrice->quantity) ? $orderPrice->quantity : 1;
    }

    public function calculateTax($amount)
    {
        return round($amount / 100 * $this->tax_percent);
    }


    public function confirmAppointments()
    {
        foreach ($this->prices as $charge) {
            AppointmentNew::confirm($charge->appointment_id, true, $this->client);
        }
    }

    public function complete($save = true)
    {
        $this->confirmAppointments();

        $this->setPaid();

        $this->decrementOwes();

        if ($save) {
            $this->save();
        }

        do_action('wappointment_order_completed', $this);
        return $this;
    }

    public function decrementOwes()
    {
        if ($this->isOnSite()) {
            $options = $this->client->options;

            //set client owing
            $options['owes'] -= $this->total;
            if ($options['owes'] < 0) {
                $options['owes'] = 0;
            }
            $this->client->options = $options;
            $this->client->save();
        }
    }

    public function refund()
    {
        if (!$this->isOnSite()) {
            //refund
            do_action('wappointment_order_refund', $this);
        }
        do_action('wappointment_order_refunded', $this);
        $this->cancelAllConnected();

        $this->setRefund();
        $this->save();
    }
}
