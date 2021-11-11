<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\Services\Settings;
use Wappointment\Services\DateTime;
use Wappointment\ClassConnect\Carbon;
use Wappointment\Messages\EmailHelper;
use Wappointment\Services\AppointmentNew as ServicesAppointment;
use Wappointment\Services\VersionDB;

class Appointment extends Model
{
    protected $table = 'wappo_appointments';

    protected $fillable = [
        'start_at', 'end_at', 'edit_key', 'client_id',
        'status', 'type', 'staff_id', 'service_id', 'options', 'location_id', 'created_at', 'updated_at',
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

    protected $appends = ['duration_sec', 'location_label', 'can_cancel_until', 'can_reschedule_until'];
    protected $services = [];
    private $shared_client = null;
    private $additional_params = null;

    public function setSharedClient($client)
    {
        $this->shared_client = $client;
    }

    public function getClientModel()
    {
        return !empty($this->shared_client) ? $this->shared_client : $this->client;
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function order()
    {
        return $this->belongsToMany(Order::class, 'wappo_order_price', 'appointment_id', 'order_id');
    }

    public function getStaff()
    {
        if (VersionDB::isLessThan(VersionDB::CAN_CREATE_SERVICES)) {
            return \Wappointment\Services\Staff::getById($this->staff_id);
        } else {
            return new \Wappointment\WP\Staff((int)$this->staff_id);
        }
    }

    public function getStaffCustomField($tagInfo = false)
    {
        return !empty($this->getStaff()->staff_data['options']['custom_fields'][$tagInfo['key']]) ? $this->getStaff()->staff_data['options']['custom_fields'][$tagInfo['key']] : '';
    }

    public function getStaffName()
    {
        return $this->getStaff()->staff_data['name'];
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

    public function getTitle($includes_buffer = true)
    {
        return $this->getStatusTag() .
            $this->getServiceName() . ' ' .
            $this->getDuration() .
            ($includes_buffer ?
                $this->getBuffer() :
                '') . ' - ' . $this->getClientModel()->name;
    }

    public function getStatusTag()
    {
        return $this->isPending() ? '[Pending]' : '';
    }
    public function isPending()
    {
        return $this->status == static::STATUS_AWAITING_CONFIRMATION;
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
        return ServicesAppointment::getLocation($location, $this);
    }

    public function toArraySpecial()
    {
        $array = parent::toArray();

        $array['start_at'] = $this->start_at->timestamp;
        $array['end_at'] = $this->end_at->timestamp;
        $array['type'] = $this->getLocationSlug();
        $array['client'] = $this->getClientModel(); //important for save to calendar button
        $array['video_meeting'] = $this->videoAppointmentHasLink();
        $staff = $this->getStaff();
        $array['ics_organizer'] = 'ORGANIZER;CN=' . $staff->staff_data['name'] . ':mailto:' . $staff->emailAddress();
        if (!empty($array['options']['providers'])) {
            unset($array['options']['providers']);
        }
        unset($array['id']);
        return $array;
    }

    public function boughtWithPackage()
    {
        return !empty($this->options['buying_package']);
    }

    public function packageVariation()
    {
        return $this->options['package_price_id'];
    }

    protected function getLocationObject()
    {
        return !empty($this->location) ? $this->location : Location::find($this->location_id);
    }

    public function getLocationVideo()
    {
        if ($this->location_id > 0) {
            $location = $this->getLocationObject();
            return !empty($location) && !empty($location->options['video']) ? $location->options['video'] : false;
        } else {
            return $this->getLocationVideoLegacy();
        }
    }

    public function getLocationVideoLegacy()
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

    public function getStaffId()
    {
        return VersionDB::canServices() ? $this->staff_id : Settings::get('activeStaffId');
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

    public function setAdditionalLinkParams($added_params)
    {
        $this->additional_params .= $added_params;
    }
    public function additionalLinkParams()
    {
        return $this->additional_params;
    }

    private function getPageLink($view = 'reschedule-event')
    {
        $editKey = $this->edit_key;
        return (new EmailHelper)->getLinkEvent($view) . (empty($editKey) ? '' : '&appointmentkey=' . $editKey) . $this->additionalLinkParams();
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
            $services[$this->service_id] = \Wappointment\Services\Services::getObject($this->service_id);
        }
        return $services[$this->service_id];
    }

    public function getServiceAddress()
    {
        return ServicesAppointment::getAddress($this->getService()->address, $this);
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

    public function videoAppointmentHasLink()
    {
        return $this->isZoom() ? $this->getMeetingLink() : false;
    }

    public function getVideoProvider()
    {
        return $this->getLocationVideo() == 'zoom' ? 'zoom' : 'google';
    }

    public function getMeetingLink()
    {
        $video_provider = $this->getVideoProvider();
        $url_meeting_key = in_array($video_provider, ['zoom']) ? 'join_url' : 'google_meet_url';

        return $this->canShowLink() && !empty($video_provider) &&
            !empty($this->options['providers']) &&
            !empty($this->options['providers'][$video_provider]) &&
            !empty($this->options['providers'][$video_provider][$url_meeting_key]) ? $this->options['providers'][$video_provider][$url_meeting_key] : false;
    }

    public function isOver()
    {
        return $this->end_at->getTimestamp() - time() < 0;
    }

    public function canShowLink()
    {
        $when_shows_link = (int)Settings::get('video_link_shows');
        if (($when_shows_link > 0 && $this->start_at->timestamp - ($when_shows_link * 60) > time()) || $this->isOver()) {
            return false;
        }
        return true;
    }

    public function getLinkViewEvent()
    {
        //if meeting link is already present return it directly.
        $link = $this->videoAppointmentHasLink();
        return $link ? $link : $this->getPageLink('view-event');
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

    public function isConfirmed()
    {
        return $this->status === self::STATUS_CONFIRMED;
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

    public function sentToDotCom()
    {
        $options = $this->options;
        $options['providers'] = [];
        $this->options = $options;
        $this->save();
    }

    public function toDotcom($timezone)
    {
        $toDotcom = [
            'title' => $this->getTitle(false),
            'type' => $this->type,
            'video' => $this->getLocationVideo(),
            'starts_at' => $this->start_at->timestamp,
            'appointment_id' => $this->id,
            'duration' => $this->getFullDurationInSec(),
            'location' => $this->type == 0 ? $this->getServiceAddress() : $this->getLocation(),
            'timezone' => $timezone,
            'emails' => [
                $this->getClientModel()->email
            ],
            'cancellink' => $this->getLinkCancelEvent(),
            'reschedulelink' => $this->getLinkRescheduleEvent(),
        ];
        if ($this->isZoom()) {
            $toDotcom['viewlink']  = $this->getLinkViewEvent();
        }
        if ($this->isPhone()) {
            $toDotcom['phone']  = $this->getClientModel()->getPhone();
        }
        if ($this->isSkype()) {
            $toDotcom['skype']  = $this->getClientModel()->getSkype();
        }
        return $toDotcom;
    }

    public function getDurationsPriceIds()
    {
        $ids = [];
        foreach ($this->services as $service) {
            $ids[] = $service->getDurationPriceId($this->getDurationInSec() / 60);
        }
        return $ids;
    }

    public function getServicesPrices()
    {
        return $this->filterPrices($this->queryPrices(
            Price::isService(),
            $this->getDurationsPriceIds()
        ));
    }

    public function paidWithPackage()
    {
        return !empty($this->options['package_price_id']);
    }

    public function getPackagePricesId()
    {
        return $this->paidWithPackage() ? [$this->options['package_price_id']] : false;
    }

    public function getPackagePrices()
    {
        return $this->filterPrices($this->queryPrices(
            Price::isPackage(),
            $this->getPackagePricesId()
        ));
    }

    public function queryPrices($query, $price_ids)
    {
        $query->where(function ($query) use ($price_ids) {
            $query->whereIn('parent', $price_ids);
            $query->orWhereIn('id', $price_ids);
        });
        $staff_id = $this->staff_id;
        return $query->where(function ($query) use ($staff_id) {
            $query->whereNull('staff_id');
            $query->orWhere('staff_id', $staff_id);
        })->get();
    }

    public function filterPrices($prices)
    {
        //remove overriden prices
        $getOverridedIds = $prices->filter(function ($e) {
            return !empty($e->staff_id);
        })->map(function ($e) {
            return $e->parent;
        });
        $overridedIds = array_values($getOverridedIds->toArray());
        return $prices->filter(function ($e) use ($overridedIds) {
            return !in_array($e->id, $overridedIds);
        });
    }

    public function hydrateService($services)
    {
        $this->services = !is_array($services) ? [$services] : $services;
    }

    public function recordOrderReference(Order $order)
    {
        $options = $this->options;
        $options['order_id'] = $order->id;
        $this->options = $options;
        $this->save();
    }
}
