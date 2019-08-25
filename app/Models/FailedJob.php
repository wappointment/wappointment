<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;

class FailedJob extends Model
{
    protected $table = 'wappo_failed_jobs';
    protected $fillable = [
        'queue', 'payload', 'exception', 'failed_at', 'appointment_id'
    ];
    protected $casts = [
        'payload' => 'array',
        'exception' => 'array',
    ];
    protected $attributes = [
        'queue' => 'default',
    ];
    public $timestamps = false;
}
