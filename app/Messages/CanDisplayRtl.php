<?php

namespace Wappointment\Messages;

trait CanDisplayRtl
{
    private $rtl = false;

    public function setRtl()
    {
        $this->rtl = true;
    }

    public function isRtl()
    {
        return $this->rtl;
    }

    public function detectRtlFromParams()
    {
        if (!empty($this->params['client']) && !empty($this->params['client']['options']['rtl'])) {
            $this->setRtl();
        }
    }
}
