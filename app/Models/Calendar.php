<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\ClassConnect\SoftDeletes;

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

    public function getCalUrls()
    {
        return !empty($this->options['cal_urls']) ? $this->options['cal_urls'] : [];
    }

    public function getRegav()
    {
        return !empty($this->options['regav']) ? $this->options['regav'] : [];
    }

    public function getTimezone()
    {
        return !empty($this->options['timezone']) ? $this->options['timezone'] : 'UTC';
    }
}
