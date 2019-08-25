<?php

namespace Wappointment\Events;

class AbstractEvent extends \Symfony\Component\EventDispatcher\Event
{

    protected function getWPAction()
    {
        if (empty(get_called_class()::NAME)) return false;
        return strtolower('wappointment_' . str_replace('.', '_', get_called_class()::NAME));
    }

    public function callWPAction()
    {
        $action = $this->getWPAction();
        if ($action) {
            do_action($action, $this);
        }
    }
}
