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

    protected function formatDuration($durationInMin)
    {
        /* translators: %s - minutes */
        return sprintf(__('%s min', 'wappointment'), $durationInMin);
    }
    public function getDuration()
    {
        return $this->formatDuration($this->getDurationInSec() / 60);
    }

    public function getBufferInSec()
    {
        return  isset($this->options) && isset($this->options['buffer_time']) ? ((int) $this->options['buffer_time']) * 60 : 0;
    }

    public function getEndTimeWithoutBuffer()
    {
        if (!empty($this->options['buffer_time'])) {
            return $this->end_at->subMinutes($this->options['buffer_time']);
        }
        return $this->end_at;
    }

    public function getBuffer()
    {
        $buffer = $this->getBufferInSec();
        return $buffer > 0 ? '(+' . $this->formatDuration($buffer / 60) . ')' : '';
    }

    public function getStartsDayAndTime($timezone)
    {
        if (empty($this->start_at)) {
            return '';
        }
        $datetime = DateTime::i18nDateTime($this->start_at->timestamp, $timezone);
        return $datetime;
    }

    public function isOver()
    {
        return $this->end_at->getTimestamp() - time() < 0;
    }
}
