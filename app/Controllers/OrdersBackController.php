<?php

namespace Wappointment\Controllers;

use Wappointment\ClassConnect\Request;
use Wappointment\Models\Order as OrderModel;
use Wappointment\Services\Settings;

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
}
