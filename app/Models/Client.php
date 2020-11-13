<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\Services\Settings;
use Wappointment\Services\Appointment as AppointmentService;
use Wappointment\Services\Service;
use Wappointment\ClassConnect\ClientSoftDeletes as SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $table = 'wappo_clients';

    protected $fillable = [
        'name', 'email', 'options'
    ];
    protected $casts = [
        'options' => 'array',
    ];

    protected $appends = ['avatar'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function getEmailAttribute($value)
    {
        return sanitize_email($value);
    }

    public function getFirstName()
    {
        return (strpos($this->name, ' ')) !== false ? substr($this->name, 0, strpos($this->name, ' ')) : $this->name;
    }

    public function getAvatarAttribute()
    {
        return get_avatar_url($this['email'], ['size' => 40]);
    }

    public function getLastName()
    {
        return (strpos($this->name, ' ')) !== false ? substr($this->name, strpos($this->name, ' ')) : '';
    }

    public function getPhone()
    {
        return empty($this->options['phone']) ? '' : $this->options['phone'];
    }

    public function getSkype()
    {
        return empty($this->options['skype']) ? '' : $this->options['skype'];
    }

    public function getTimezone()
    {
        return empty($this->options['tz']) ? Settings::getStaff('timezone') : $this->options['tz'];
    }

    public function book($bookingRequest, $forceConfirmed = false)
    {
        $startTime = $bookingRequest->get('time');
        $type = $bookingRequest->get('type');
        $service = Service::get();

        //test type is allowed
        if (!in_array($type, $service['type'])) {
            throw new \WappointmentException('Error booking type not allowed2', 1);
        }

        $type = (int) call_user_func('Wappointment\Models\Appointment::getType' . ucfirst($type));

        //test that this is bookable
        if ($forceConfirmed) {
            $hasBeenBooked = AppointmentService::adminBook(
                $this,
                $startTime,
                $startTime + $this->getRealDuration($service),
                $type,
                $service
            );
        } else {
            $hasBeenBooked = AppointmentService::tryBook(
                $this,
                $startTime,
                $startTime + $this->getRealDuration($service),
                $type,
                $service
            );
        }

        if (!$hasBeenBooked) {
            throw new \WappointmentException('Error cannot book at this time', 1);
        }
        return $hasBeenBooked;
    }

    protected function getRealDuration($service)
    {
        return ((int) $service['duration'] + (int) Settings::get('buffer_time')) * 60;
    }

    public function mailableAddress()
    {
        return [$this->email => sanitize_text_field($this->name)];
    }
}
