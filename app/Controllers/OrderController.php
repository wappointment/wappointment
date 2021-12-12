<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Formatters\BookingResult;
use Wappointment\Services\Order as ServicesOrder;

class OrderController extends RestController
{
    public function confirm(Request $request)
    {
        $orderService = new ServicesOrder($request->input('transaction_id'));
        $orderService->awaitPayment();
        $orderService->order->appointments[0]->refresh();
        return BookingResult::format([
            'appointment' => $orderService->order->appointments[0]
        ]);
    }
}
