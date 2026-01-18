<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\ClassConnect\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    public const TYPE_AT_LOCATION = 1;
    public const TYPE_PHONE = 2;
    public const TYPE_OTHERS = 4;
    public const TYPE_ZOOM = 5;
    public const STATUS_ARCHIVED = -1;
    public const STATUS_DEFAULT = 0;

    protected $table = 'wappo_locations';
    protected $fillable = [
        'name', 'type', 'status',  'options',
    ];
    protected $hidden = ['pivot'];

    public function services()
    {
        return $this->belongsToMany('Wappointment\Models\Service', 'wappo_service_location');
    }
}
