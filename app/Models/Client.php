<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\Services\Settings;
use Wappointment\ClassConnect\ClientSoftDeletes as SoftDeletes;
use Wappointment\ClassConnect\Carbon;

class Client extends Model
{
    use SoftDeletes, CanBook, CanBookLegacy;

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

    public function hasActiveBooking()
    {
        $start_at_string = Carbon::now('UTC')->format(WAPPOINTMENT_DB_FORMAT);
        return Appointment::where('client_id', $this->id)
            ->where('start_at', '>=', $start_at_string)->count();
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

    public function getCustomField($tag = false)
    {
        return empty($tag) ? '' : $this->options[$tag['key']];
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
