<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\ClassConnect\SoftDeletes;
use Wappointment\WP\Staff;

class Calendar extends Model
{
    use SoftDeletes, CanLimit;
    protected $dates = ['deleted_at'];
    protected $table = 'wappo_calendars';
    protected $with = ['services'];
    protected $visible = ['id', 'wp_uid', 'name', 'options', 'services', 'sorting',  'availability'];
    protected $fillable = ['name', 'wp_uid', 'options', 'sorting', 'availability', 'account_key'];
    protected $casts = [
        'options' => 'array',
        'availability' => 'array',
    ];

    public function services()
    {
        return $this->belongsToMany('Wappointment\Models\Service', 'wappo_calendar_service');
    }
}
