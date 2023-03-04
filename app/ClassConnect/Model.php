<?php

namespace Wappointment\ClassConnect;

class Model extends \Illuminate\Database\Eloquent\Model
{
    protected $casts = [
        'options' => 'array',
        'availability' => 'array',
        'locked' => 'boolean',
        'published' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'paid_at' => 'datetime:Y-m-d H:i:s',
        'refunded_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
