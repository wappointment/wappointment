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

    public const TYPE_FREE = 0;
    public const TYPE_BUSY = 1;

    public const RECUR_NOT = 0;
    public const RECUR_DAILY = 1;
    public const RECUR_WEEKLY = 2;
    public const RECUR_MONTHLY = 3;
    public const RECUR_YEARLY = 4;

    protected $fillable = [
        'type', 'start_at', 'end_at', 'source', 'recur', 'staff_id', 'muted', 'options'
    ];

    protected $dates = [
        'start_at', 'end_at'
    ];
}
