<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;

class Job extends Model
{
    protected $table = 'wappo_jobs';
    protected $fillable = [
        'queue', 'payload', 'attempts', 'appointment_id', 'reserved_at', 'available_at', 'created_at'
    ];
    protected $casts = [
        'payload' => 'array',
    ];
    protected $attributes = [
        'queue' => 'default',
        'attempts' => 0,
        'available_at' => 0,
        'created_at' => 0,
    ];
    public $timestamps = false;
}
