<?php

namespace Wappointment\Messages;

use Wappointment\Messages\Templates\Order as OrderMessage;
use Wappointment\Models\Order;
use Wappointment\Services\Payment;
use Wappointment\Services\Settings;
// @codingStandardsIgnoreFile
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

    protected static function getOrderDate($order)
    {
        return sprintf(__('Date: %s'), $order->created_at->format(Settings::get('date_format')));
    }

    protected static function getClientData($order)
    {
        $client_data = __('Client', 'wappointment') . '<br/>';
        $cfields = Settings::get('invoice_client');
        foreach ($cfields as $fieldkey) {
            $hasClientData = static::hasClientData($order->options['client'], $fieldkey);
            if ($hasClientData) {
                $client_data .=  esc_html($hasClientData) . '<br/>';
            }
        }

        return $client_data;
    }

    protected static function hasClientData($client, $key)
    {
        return isset($client[$key]) ? $client[$key] : static::hasClientDataInOptions($client, $key);
    }

    protected static function hasClientDataInOptions($client, $key)
    {
        return isset($client['options'][$key]) ? $client['options'][$key] : false;
    }

    protected static function generateOrderTable($params)
    {
        if (!empty($params['order'])) {
            $order = Order::find($params['order']['id']); //fully hydrated object
        }
        if (empty($order)) {
            return '';
        }
        $rows = [];
        if (Settings::get('invoice')) {
            $rows[] = [
                'cells' => ['', '', Settings::get('invoice_num') . $order->id . '<br/>' . static::getOrderDate($order)],
                'class' => '',
                'cellClass' => 'muted'
            ];
            $rows[] = [
                'cells' => [nl2br(esc_html(Settings::get('invoice_seller'))), '', static::getClientData($order)],
                'class' => 'linesep',
                'cellClass' => 'muted'
            ];
        }

        $rows[] = [
            'cells' => [__('Service', 'wappointment'), __('Price', 'wappointment'), __('Quantity', 'wappointment')],
            'class' => 'lineb',
            'cellClass' => 'muted'
        ];

        foreach ($order->prices as $price) {
            $rows[] = [$price->price->name, Payment::formatPrice($price->price->price / 100), $price->quantity];
        }

        if ($order->tax_amount > 0) {
            $rows[] = [__('Tax', 'wappointment'), Payment::formatPrice(round($order->tax_amount / 100, 2)), ''];
        }

        $rows[] = [
            'cells' => [__('Total', 'wappointment'), Payment::formatPrice(($order->total + $order->tax_amount) / 100), ''],
            'class' => 'bold lineb linet'
        ];
        $rows[] = [
            'cells' => [__('Status', 'wappointment'), $order['payment_label'] . ' - ' . $order['status_label'], ''],
            'class' => 'small'
        ];

        return OrderMessage::table($rows);
    }

    protected function getStaffId($params)
    {
        return empty($params['appointment']) ? false : $params['appointment']['staff_id'];
    }
}
