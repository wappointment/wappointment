<?php

namespace Wappointment\Models\Appointment;

use Wappointment\Services\DateTime;

trait ManipulateDuration
{
    public function getFullDurationInSec()
    {
        return !empty($this->end_at) ? $this->end_at->timestamp - $this->start_at->timestamp : 0;
    }

    public function getDurationSecAttribute()
    {
        return $this->getDurationInSec();
    }

    public function getDurationInSec()
    {
        return $this->getFullDurationInSec() - $this->getBufferInSec();
    }

    public function getDuration()
    {
        return ($this->getDurationInSec() / 60) . __('min', 'wappointment');
    }

    public function getBufferInSec()
    {
        return  isset($this->options) && isset($this->options['buffer_time']) ? ((int) $this->options['buffer_time']) * 60 : 0;
    }

    public function getBuffer()
    {
        $buffer = $this->getBufferInSec();
        return $buffer > 0 ? '(+' . ($buffer / 60) .  __('min', 'wappointment') . ')' : '';
    }

    public function getStartsDayAndTime($timezone)
    {
        return !empty($this->start_at) ? DateTime::i18nDateTime($this->start_at->timestamp, $timezone) : '';
    }

    public function isOver()
    {
        return $this->end_at->getTimestamp() - time() < 0;
    }
}
