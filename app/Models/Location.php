<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\ClassConnect\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    const TYPE_AT_LOCATION = 1;
    const TYPE_PHONE = 2;
    const TYPE_SKYPE = 3;
    const TYPE_OTHERS = 4;
    const TYPE_ZOOM = 5;
    const STATUS_ARCHIVED = -1;
    const STATUS_DEFAULT = 0;

    protected $table = 'wappo_locations';
    protected $fillable = [
        'name', 'type', 'status',  'options',
    ];
    protected $hidden = ['pivot'];
    protected $casts = [
        'options' => 'array',
    ];

    public function services()
    {
        return $this->belongsToMany('Wappointment\Models\Service', 'wappo_service_location');
    }
}
