<?php

namespace Wappointment\Models\Appointment;

use Wappointment\Services\Settings;
use Wappointment\ClassConnect\Carbon;

trait ManipulateCancelReschedule
{
    public function getCanRescheduleUntilAttribute()
    {
        if (Settings::get('allow_rescheduling')) {
            return $this->canRescheduleUntilTimestamp();
        }
    }

    public function getCanCancelUntilAttribute()
    {
        if (Settings::get('allow_cancellation')) {
            return $this->canCancelUntilTimestamp();
        }
    }

    public function canRescheduleUntilTimestamp()
    {
        return $this->start_at->getTimestamp() - ((float) Settings::get('hours_before_rescheduling_allowed') * 60 * 60);
    }

    public function canCancelUntilTimestamp()
    {
        return $this->start_at->getTimestamp() - ((float) Settings::get('hours_before_cancellation_allowed') * 60 * 60);
    }

    public function canStillReschedule()
    {
        return ($this->canRescheduleUntilTimestamp() - time()) > 0;
    }

    public function canStillCancel()
    {
        return !$this->isConfirmed() || ($this->canCancelUntilTimestamp() - time()) > 0;
    }

    public function cancelLimit()
    {
        return Carbon::createFromTimestamp($this->canCancelUntilTimestamp())
            ->setTimezone($this->getClientModel()->getTimezone())->format($this->longFormat());
    }

    public function rescheduleLimit()
    {
        return Carbon::createFromTimestamp($this->canRescheduleUntilTimestamp())
            ->setTimezone($this->getClientModel()->getTimezone())->format($this->longFormat());
    }

    protected function longFormat()
    {
        return Settings::get('date_format') . Settings::get('date_time_union') . Settings::get('time_format');
    }
}
