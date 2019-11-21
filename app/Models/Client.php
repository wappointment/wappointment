<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\Services\Settings;
use Wappointment\Services\Appointment as AppointmentService;
use Wappointment\Services\Service;

class Client extends Model
{
    protected $table = 'wappo_clients';

    protected $fillable = [
        'name', 'email', 'options'
    ];
    protected $casts = [
        'options' => 'array',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    public function getFirstName()
    {
        return (strpos($this->name, ' ')) !== false ? substr($this->name, 0, strpos($this->name, ' ')) : $this->name;
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
        return empty($this->options['tz']) ? '' : $this->options['tz'];
    }

    public function book($startTime, $type, $service = false)
    {
        if ($service === false) {
            $service = Service::get();
        }

        //test type is allowed
        if (!in_array($type, $service['type'])) {
            throw new \WappointmentException('Error booking type not allowed', 1);
        }

        $type = (int) call_user_func('Wappointment\Models\Appointment::getType' . ucfirst($type));

        //test that this is bookable
        $hasBeenBooked = AppointmentService::tryBook($this, $startTime, $startTime + ((int) $service['duration'] * 60), $type, $service);
        if (!$hasBeenBooked) {
            throw new \WappointmentException('Error cannot book at this time', 1);
        }
        return $hasBeenBooked;
    }

    public function mailableAddress()
    {
        return [$this->email => $this->name];
    }
}
