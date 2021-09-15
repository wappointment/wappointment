<?php

namespace Wappointment\Messages;

use Wappointment\Messages\Templates\Order as OrderMessage;
use Wappointment\Services\Payment;
use Wappointment\Services\Settings;

class EmailHelper
{
    public function getOrderTable($params)
    {
        //return order table only if there is an appointment param and it's not woocoomerce
        if (!empty($params['appointment']) && !is_array($params['appointment']) && empty($params['appointment']->options['woo_order_id'])) {
            return $this->generateOrderTable($params);
        }
        return '';
    }

    public function getLinkNewEventStaff($params)
    {
        return $this->getLinkNewEvent() . '&staff=' . $this->getStaffId($params);
    }

    public function getLinkNewEvent()
    {
        return $this->getLinkEvent('new-event');
    }

    public function getLinkEvent($view = 'new-event')
    {
        static $page_link = '';
        if (empty($page_link)) {
            $page_link = get_permalink(Settings::get('front_page'));
            $page_link .= strpos($page_link, '?') !== false ? '&' : '?';
        }

        return $page_link . 'view=' . $view;
    }

    protected static function generateOrderTable($params)
    {
        $params['appointment']->load('order');
        $order = $params['appointment']->order->first();
        $rows = [
            [
                'cells' => [__('Service', 'wappointment'), __('Price', 'wappointment')],
                'class' => 'lineb',
                'cellClass' => 'muted'
            ],
        ];
        if (!empty($order)) {
            foreach ($order->prices as $price) {
                $rows[] = [$price->price->name, Payment::formatPrice($price->price->price / 100)];
            }

            if ($order->tax_amount > 0) {
                $rows[] = ['Tax', Payment::formatPrice(round($order->tax_amount / 100, 2))];
            }

            $rows[] = [
                'cells' => ['Total', Payment::formatPrice(($order->total + $order->tax_amount) / 100)],
                'class' => 'bold lineb linet'
            ];
            $rows[] = [
                'cells' => ['Status', $order['payment_label'] . ' - ' . $order['status_label']],
                'class' => 'small'
            ];
        }

        return OrderMessage::table($rows);
    }

    protected function getStaffId($params)
    {
        return empty($params['appointment']) ? false : $params['appointment']['staff_id'];
    }
}
