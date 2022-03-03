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

    public static function parseUserObject($userObject)
    {
        $keys_allowed = ['ID', 'user_login', 'user_nicename', 'display_name', 'user_email'];
        $user_data = [];
        foreach ($keys_allowed as $key) {
            if ($key == 'ID') {
                $user_data[$key] = (int)$userObject->data->$key;
            } else {
                $user_data[$key] = $userObject->data->$key;
            }
        }
        $roles = array_values(!is_array($userObject->roles) ? (array) $userObject->roles : $userObject->roles);

        $user_data['role'] = $roles[0];
        $user_data['gravatar'] = static::getGravatar($userObject->data->ID);
        return $user_data;
    }

    public static function getGravatar($id)
    {
        return get_avatar_url($id, ['size' => 40]);
    }
    public function getGravatarAttribute()
    {
        return static::getGravatar($this->id);
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
