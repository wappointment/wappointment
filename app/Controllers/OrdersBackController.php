<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Models\Order as OrderModel;
use Wappointment\Services\Settings;
use Wappointment\Services\Order as ServicesOrder;

class OrdersBackController extends RestController
{

    public function index(Request $request)
    {
        if (!empty($request->input('per_page'))) {
            Settings::saveStaff('per_page', $request->input('per_page'));
        }

        return [
            'page' => $request->input('page'),
            'viewData' => [
                'per_page' => Settings::getStaff('per_page'),
                'tax' => Settings::get('tax'),
            ],
            'orders' => $this->getOrders()
        ];
    }

    protected function getOrders()
    {
        $query = OrderModel::orderBy('id', 'DESC');


        return $query->paginate(Settings::getStaff('per_page'));
    }


    public function refund(Request $request)
    {
        ServicesOrder::refund($request->input('order_id'));
        return ['message' => 'Order has been refunded'];
    }

    public function markAsPaid(Request $request)
    {

        ServicesOrder::markPaid($request->input('order_id'), $request->input('purchase_info'));

        return ['message' => 'Order has been paid'];
    }

    public function cancel(Request $request)
    {

        ServicesOrder::cancel($request->input('order_id'), $request->input('cancel_info'));

        return ['message' => 'Order has been cancelled'];
    }
}
