<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\Services\Settings;
use Wappointment\ClassConnect\Carbon;

class Appointment extends Model
{
    protected $table = 'wappo_appointments';

    protected $fillable = [
        'start_at', 'end_at', 'edit_key', 'client_id', 'status', 'type', 'staff_id', 'service_id', 'options'
    ];
    protected $casts = [
        'options' => 'array',
    ];
    protected $dates = [
        'start_at', 'end_at'
    ];

    const TYPE_PHYSICAL = 0;
    const TYPE_PHONE = 1;
    const TYPE_SKYPE = 2;
    /* const STATUS_ADMIN_CANCELLED = -2;
    const STATUS_CANCELLED = -1; */
    const STATUS_AWAITING_CONFIRMATION = 0;
    const STATUS_CONFIRMED = 1;


    public function getStringType()
    {
        switch ($this->type) {
            case self::TYPE_PHYSICAL:
                return 'physical';
            case self::TYPE_PHONE:
                return 'phone';
            case self::TYPE_SKYPE:
                return 'skype';
        }
    }

    public function getStaffId()
    {
        return Settings::get('activeStaffId');
    }

    public function isPhone()
    {
        return self::TYPE_PHONE == $this->type;
    }

    public function isPhysical()
    {
        return self::TYPE_PHYSICAL == $this->type;
    }

    public function isSkype()
    {
        return self::TYPE_SKYPE == $this->type;
    }

    public static function getTypePhysical()
    {
        return self::TYPE_PHYSICAL;
    }

    public static function getTypePhone()
    {
        return self::TYPE_PHONE;
    }

    public static function getTypeSkype()
    {
        return self::TYPE_SKYPE;
    }

    public function getDurationInSec()
    {
        return $this->end_at->timestamp - $this->start_at->timestamp;
    }

    public function getDuration()
    {
        return ($this->getDurationInSec() / 60) . 'min';
    }

    public function getStartsDayAndTime($timezone)
    {
        return $this->start_at
            ->timezone($timezone)
            ->format(Settings::get('date_format') . Settings::get('date_time_union') . Settings::get('time_format'));
    }

    public function getServiceName()
    {
        return  $this->getService()->name;
    }

    private function getPageLink($view = 'reschedule-event')
    {
        static $page_link = '';
        if ($page_link == '') {
            $page_link = get_permalink(Settings::get('front_page'));
        }
        return $page_link . ((strpos($page_link, '?') !== false) ? '&' : '?') . 'view=' . $view . '&appointmentkey=' . $this->edit_key;
    }
    public function getService()
    {
        return \Wappointment\Services\Service::get();
    }

    public function getServiceAddress()
    {
        return $this->getService()->address;
    }

    public function getLinkAddEventToCalendar()
    {
        return $this->getPageLink('add-event-to-calendar');
    }

    public function getLinkRescheduleEvent()
    {
        return $this->getPageLink();
    }

    public function getLinkCancelEvent()
    {
        return $this->getPageLink('cancel-event');
    }

    public function getLinkNewEvent()
    {
        return $this->getPageLink('new-event');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function canRescheduleUntilTimestamp()
    {
        return $this->start_at->getTimestamp() - ((int) Settings::get('hours_before_rescheduling_allowed') * 60 * 60);
    }

    public function canCancelUntilTimestamp()
    {

        return $this->start_at->getTimestamp() - ((int) Settings::get('hours_before_cancellation_allowed') * 60 * 60);
    }

    public function canStillReschedule()
    {
        return ($this->canRescheduleUntilTimestamp() - Carbon::now()->timestamp) > 0;
    }

    public function canStillCancel()
    {
        return ($this->canCancelUntilTimestamp() - Carbon::now()->timestamp) > 0;
    }
}
