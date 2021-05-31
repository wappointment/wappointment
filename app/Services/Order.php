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
        foreach ($this->order->prices as $charge) {
            AppointmentNew::confirm($charge->appointment_id);
        }
        $this->order->setProcessing();
        $this->order->save();
    }
}
