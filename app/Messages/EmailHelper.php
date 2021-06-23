<?php

namespace Wappointment\Messages;

use Wappointment\Services\Settings;

class EmailHelper
{
    public function getLinkNewEventStaff($params)
    {
        return $this->getLinkNewEvent() . '&staff=' . $this->getStaffId($params);
    }

    protected function getStaffId($params)
    {
        return empty($params['appointment']) ? false : $params['appointment']['staff_id'];
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
}
