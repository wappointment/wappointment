<?php

namespace Wappointment\Services;

use Wappointment\Models\Order as ModelOrder;

class Order
{
    public $order = null;

    public function __construct($transaction_id)
    {
        $this->order = ModelOrder::where('transaction_id', $transaction_id)
            ->firstOrFail();
    }

    public function processing()
    {
        $this->order->confirmAppointments();

        $this->order->setProcessing();
        $this->order->save();
    }

    public static function refund($order_id)
    {
        $order = ModelOrder::findOrFail($order_id);
        $order->refund();
    }
}
