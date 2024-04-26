<?php

namespace Wappointment\Models\Appointment;

use Wappointment\Services\Settings;
use Wappointment\ClassConnect\Carbon;
use Wappointment\Services\DateTime;
// @codingStandardsIgnoreFile
trait ManipulateCancelReschedule
{
    public function getCanRescheduleUntilAttribute()
    {
        if (Settings::get('allow_rescheduling')) {
            return $this->canRescheduleUntilTimestamp();
        }
    }

    public function getRescheduleUntilTextAttribute()
    {
        return sprintf(
            __('Reschedule (until %1$s): &#10; %2$s', 'wappointment'),
            $this->rescheduleLimit(),
            $this->getLinkRescheduleEvent()
        );
    }

    public function getCancelUntilTextAttribute()
    {
        return sprintf(
            __('Cancel (until %1$s): &#10; %2$s', 'wappointment'),
            $this->cancelLimit(),
            $this->getLinkCancelEvent()
        );
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
        return DateTime::i18nDateTime($this->canCancelUntilTimestamp(), $this->getStaffTZ());
    }

    public function rescheduleLimit()
    {
        return DateTime::i18nDateTime($this->canRescheduleUntilTimestamp(), $this->getStaffTZ());
    }

    protected function longFormat()
    {
        return Settings::get('date_format') . Settings::get('date_time_union') . Settings::get('time_format');
    }
}
