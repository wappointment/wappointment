<?php

namespace Wappointment\Models;

trait CanLock
{

    public function lock()
    {
        $options = $this->options;
        $options['locked'] = true;
        $this->options = $options;
        $this->save();
    }

    public function isLocked()
    {
        return !empty($this->options['locked']);
    }
}
