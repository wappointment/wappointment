<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Services\Order as ServicesOrder;

class OrderController extends RestController
{

    public function confirm(Request $request)
    {
        $orderService = new ServicesOrder($request->input('transaction_id'));
        $orderService->processing();
    }

    public function refund(Request $request)
    {
        ServicesOrder::refund($request->input('order_id'));
        return ['message' => 'Order has been refunded'];
    }
}
