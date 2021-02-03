<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\Services\Settings;
use Wappointment\Services\DateTime;
use Wappointment\ClassConnect\Carbon;

class Appointment extends Model
{
    protected $table = 'wappo_appointments';

    protected $fillable = [
        'start_at', 'end_at', 'edit_key', 'client_id',
        'status', 'type', 'staff_id', 'service_id', 'options', 'location_id', 'created_at', 'updated_at'
    ];
    protected $casts = [
        'options' => 'array',
    ];
    protected $dates = [
        'start_at', 'end_at', 'created_at', 'updated_at',
    ];

    const TYPE_PHYSICAL = 0;
    const TYPE_PHONE = 1;
    const TYPE_SKYPE = 2;
    const TYPE_ZOOM = 5;
    const STATUS_AWAITING_CONFIRMATION = 0;
    const STATUS_CONFIRMED = 1;

    protected $appends = ['duration_sec', 'location_label'];

    public function getStaff()
    {
        return \Wappointment\Services\Staff::getById($this->staff_id);
    }
    public function getLocationSlug()
    {
        switch ($this->type) {
            case self::TYPE_PHYSICAL:
                return 'physical';
            case self::TYPE_PHONE:
                return 'phone';
            case self::TYPE_SKYPE:
                return 'skype';
            case self::TYPE_ZOOM:
                return 'zoom';
        }
    }

    public function getSequence()
    {
        return empty($this->options['sequence']) ? 0 : $this->options['sequence'];
    }

    public function getTitle()
    {
        return $this->getStatusTag() . $this->getServiceName() . ' ' . $this->getDuration() . $this->getBuffer() . ' - ' . $this->client->name;
    }

    public function getStatusTag()
    {
        return $this->status == static::STATUS_AWAITING_CONFIRMATION ? '[Pending]' : '';
    }

    public function incrementSequence()
    {
        $this->options = $this->getIncrementedSequenceOptions();
        return $this->save();
    }

    public function getIncrementedSequenceOptions()
    {
        $options = $this->options;
        $options['sequence'] = $this->getSequence() + 1;
        return $options;
    }

    public function getLocation()
    {
        $location = '';
        switch ($this->type) {
            case self::TYPE_PHYSICAL:
                $location = 'Address: ' . $this->getServiceAddress();
                break;
            case self::TYPE_PHONE:
                $location = 'By Phone';
                break;
            case self::TYPE_SKYPE:
                $location = 'By Skype';
                break;
            case self::TYPE_ZOOM:
                $location = 'Video meeting';
                break;
        }
        return apply_filters('wappointment_service_location', $location, $this);
    }

    public function getLocationVideo()
    {
        return $this->type == self::TYPE_ZOOM ? $this->getServiceVideo() : false;
    }
    public function getServiceVideo()
    {
        return $this->getService()->getVideo();
    }

    public function getLocationLabelAttribute()
    {
        return $this->getLocation();
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

    public function isZoom()
    {
        return self::TYPE_ZOOM == $this->type;
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

    public static function getTypeZoom()
    {
        return self::TYPE_ZOOM;
    }

    public function toArraySpecial()
    {
        $appointment = parent::toArray();

        $appointment['start_at'] = $this->start_at->timestamp;
        $appointment['end_at'] = $this->end_at->timestamp;
        $appointment['type'] = $this->getLocationSlug();
        $appointment['converted'] = DateTime::i18nDateTime((int) $appointment['start_at'], $this->client->getTimezone());

        return $appointment;
    }

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
        return ($this->getDurationInSec() / 60) . 'min';
    }

    public function getBufferInSec()
    {
        return  isset($this->options) && isset($this->options['buffer_time']) ? ((int) $this->options['buffer_time']) * 60 : 0;
    }

    public function getBuffer()
    {
        $buffer = $this->getBufferInSec();
        return $buffer > 0 ? '(+' . ($buffer / 60) . 'min)' : '';
    }

    public function getStartsDayAndTime($timezone)
    {
        return !empty($this->start_at) ? DateTime::i18nDateTime($this->start_at->timestamp, $timezone) : '';
        /*         return $this->start_at
            ->timezone($timezone)
            ->format(Settings::get('date_format') . Settings::get('date_time_union') . Settings::get('time_format')); */
    }


    private function getPageLink($view = 'reschedule-event')
    {
        static $page_link = '';
        if ($page_link == '') {
            $page_link = get_permalink(Settings::get('front_page'));
        }
        return $page_link .
            ((strpos($page_link, '?') !== false) ? '&' : '?')
            . 'view=' . $view . '&appointmentkey=' . $this->edit_key;
    }

    public function getServiceName()
    {
        return  $this->getService()->name;
    }

    public function getService()
    {
        static $services = [];
        if (empty($this->service_id)) {
            return \Wappointment\Services\Service::getObject();
        }
        if (empty($services[$this->service_id])) {
            $services[$this->service_id] = apply_filters(
                'wappointment_get_appointment_service',
                \Wappointment\Services\Service::getObject(),
                $this->service_id
            );
        }
        return $services[$this->service_id];
    }

    public function getServiceAddress()
    {
        return apply_filters('wappointment_get_service_address', $this->getService()->address, $this);
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

    public function getLinkViewEvent()
    {
        return $this->getPageLink('view-event');
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

    public function isConfirmed()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function canStillCancel()
    {
        return !$this->isConfirmed() || ($this->canCancelUntilTimestamp() - Carbon::now()->timestamp) > 0;
    }

    public function cancelLimit()
    {
        return Carbon::createFromTimestamp($this->canCancelUntilTimestamp())
            ->setTimezone($this->client->getTimezone())->format($this->longFormat());
    }

    public function rescheduleLimit()
    {
        return Carbon::createFromTimestamp($this->canRescheduleUntilTimestamp())
            ->setTimezone($this->client->getTimezone())->format($this->longFormat());
    }

    protected function longFormat()
    {
        return Settings::get('date_format') . Settings::get('date_time_union') . Settings::get('time_format');
    }

    public function sentToDotCom()
    {
        $options = $this->options;
        $options['providers'] = [];
        $this->options = $options;
        $this->save();
    }
}
