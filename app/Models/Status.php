<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;

class Status extends Model
{
    use CanIgnore;
    public $timestamps = false;

    public static function boot()
    {
        parent::boot();
    }

    protected $table = 'wappo_statuses';

    const TYPE_FREE = 0;
    const TYPE_BUSY = 1;

    const RECUR_NOT = 0;
    const RECUR_DAILY = 1;
    const RECUR_WEEKLY = 2;
    const RECUR_MONTHLY = 3;
    const RECUR_YEARLY = 4;

    protected $fillable = [
        'type', 'start_at', 'end_at', 'source', 'recur', 'staff_id', 'muted'
    ];

    protected $dates = [
        'start_at', 'end_at'
    ];
    protected $casts = [
        'options' => 'array',
    ];
}
