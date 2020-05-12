<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;

class WPUser extends Model
{
    protected $table = 'users';
    protected $connection = 'ms';
    protected $fillable = [
        'user_login', 'user_pass', 'user_nicename', 'user_email', 'user_url',
        'user_register', 'user_activation',  'user_status', 'display_name'
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function mailableAddress()
    {
        return [$this->user_email => $this->display_name];
    }
}
