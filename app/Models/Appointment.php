<?php

namespace Wappointment\Models;

use Wappointment\Models\Appointment\ManipulateCancelReschedule;
use Wappointment\Models\Appointment\ManipulateDotcom;
use Wappointment\Models\Appointment\ManipulateDuration;
use Wappointment\Models\Appointment\ManipulateIcs;
use Wappointment\Models\Appointment\ManipulateLinks;
use Wappointment\Models\Appointment\ManipulateLocation;
use Wappointment\Models\Appointment\ManipulateService;
use Wappointment\Models\Appointment\ManipulateStaff;
use Wappointment\Models\Appointment\ManipulateType;
use Wappointment\Models\CanLock;
use Wappointment\Services\Availability;
use Wappointment\Services\JobHelper;

class Appointment extends TicketAbstract
{
    use CanLock, ManipulateIcs, ManipulateType, ManipulateLinks, ManipulateStaff, ManipulateDotcom, ManipulateService, ManipulateDuration, ManipulateLocation, ManipulateCancelReschedule;
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
    private $shared_client = null;

    public function order()
    {
        return $this->belongsToMany(Order::class, 'wappo_order_price', 'appointment_id', 'order_id');
    }

    public function setSharedClient($client)
    {
        $this->shared_client = $client;
    }

    public function getClientModel()
    {
        return !empty($this->shared_client) ? $this->shared_client : $this->client;
    }

    public function getClientMethodOrEmpty($key)
    {
        $cmodel = $this->getClientModel();
        if ($cmodel && !is_null($cmodel) && in_array($key, ['getPhone', 'getSkype', 'getNameForDotcom', 'getEmailForDotcom'])) {
            return call_user_func([$cmodel, $key]);
        }
        return '';
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

    public function getTitle($includes_buffer = true)
    {
        return $this->getStatusTag() .
            $this->getServiceName() . ' ' .
            $this->getDuration() .
            ($includes_buffer ?
                $this->getBuffer() :
                '') . ' - ' . $this->getClientMethodOrEmpty('getNameForDotcom');
    }

    public function getIdentifier()
    {
        return preg_replace('/[^\da-z]/i', '-', $this->getStaffName() . '-' . $this->getTitle());
    }

    public function tryDestroy($force = false)
    {
        if ($force || !$this->isLocked()) {
            //make sure there is no remaining charge connected
            $this->clearConnectedCharges();
            $this->incrementSequence();
            JobHelper::dcCancel($this);
            $this->destroy($this->id);
            (new Availability($this->getStaffId()))->regenerate();
        }
    }

    private function clearConnectedCharges()
    {
        OrderPrice::where('appointment_id', $this->id)->update(['appointment_id' => null]);
    }

    public function getStatusTag()
    {
        return $this->isPending() ? '[Pending]' : '';
    }

    public function isPending()
    {
        return $this->status == static::STATUS_AWAITING_CONFIRMATION;
    }

    public function toArraySpecial()
    {
        $array = $this->toArray();
        $array['start_at'] = $this->start_at->timestamp;
        $array['end_at'] = $this->end_at->timestamp;
        $array['type'] = $this->getLocationSlug();
        $array['client'] = $this->getClientModel(); //important for save to calendar button
        $array['video_meeting'] = $this->videoAppointmentHasLink();
        $staff = $this->getStaff();
        $array['ics_organizer'] = 'ORGANIZER;CN=' . $staff->staff_data['name'] . ':mailto:' . $staff->emailAddress();
        $array['edit_key_original'] = $array['edit_key'];
        if (!empty($array['options']['providers'])) {
            unset($array['options']['providers']);
        }
        unset($array['id']);
        return $array;
    }

    public function getAppointment()
    {
        return $this;
    }
}
