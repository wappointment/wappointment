<?php

namespace Wappointment\Messages;

use Wappointment\Messages\Templates\Order as OrderMessage;
use Wappointment\Services\Settings;

class EmailHelper
{
    public function getOrderTable($params)
    {
        //return order table only if there is an appointment param and it's not woocoomerce
        if (!empty($params['appointment']) && empty($params['appointment']->options['woo_order_id'])) {
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
        $rows = [
            ['Service', 'Price'],
            ['Consultation - 60min', '30 €'],
            ['test - 30min', '75 €'],
            ['Total', '105 €'],
        ];
        return OrderMessage::table($rows);
    }

    protected function getStaffId($params)
    {
        return empty($params['appointment']) ? false : $params['appointment']['staff_id'];
    }
}
