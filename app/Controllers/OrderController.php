<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Services\Order as ServicesOrder;

class OrderController extends RestController
{
    public function confirm(Request $request)
    {
        $orderService = new ServicesOrder($request->input('transaction_id'));
        $orderService->awaitPayment();
        $orderService->order->appointments[0]->refresh();
        return [
            'appointment' => $orderService->order->appointments[0]
        ];
    }
}
