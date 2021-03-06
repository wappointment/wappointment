<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\ClassConnect\SoftDeletes;

class Service extends Model
{
    use SoftDeletes, CanLimit;
    protected $dates = ['deleted_at'];
    protected $table = 'wappo_services';
    protected $with = ['locations'];
    protected $visible = ['id', 'name', 'options', 'locations', 'sorting'];
    protected $hidden = ['pivot'];
    protected $fillable = ['name', 'options', 'sorting'];
    protected $casts = [
        'options' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->limited = 3;
    }


    public function locations()
    {
        return $this->belongsToMany('Wappointment\Models\Location', 'wappo_service_location');
    }


    public function hasDuration($duration)
    {
        foreach ($this->options['durations'] as $key => $duration_row) {
            if ($duration_row['duration'] == $duration) {
                return $duration;
            }
        }
        throw new \WappointmentException("Error with duration", 1);
    }
}
