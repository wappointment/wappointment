<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;

class WPUser extends Model
{
    protected $table = 'users';
    protected $fillable = [
        'user_login', 'user_nicename', 'user_email', 'user_url',
        'user_register', 'user_activation',  'user_status', 'display_name'
    ];
    protected $appends = ['gravatar'];

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        if (is_multisite()) {
            $this->connection = 'ms';
        }
    }

    public function getGravatarAttribute()
    {
        return get_avatar_url($this->ID, ['size' => 40]);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function mailableAddress()
    {
        return [$this->user_email => $this->display_name];
    }
}
