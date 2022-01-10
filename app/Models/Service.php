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
    protected $visible = ['id', 'name', 'options', 'locations', 'sorting', 'labels'];
    protected $hidden = ['pivot'];
    protected $fillable = ['name', 'options', 'sorting'];
    protected $casts = [
        'options' => 'array',
    ];
    protected $appends = ['labels'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->limited = 3;
    }

    public function getLabelsAttribute()
    {
        $labels = [];
        if ($this->isSold()) {
            $labels[] = ['class' => 'text-success', 'text' => __('Selling', 'wappointment')];
        } else {
            $labels[] = ['class' => 'text-info', 'text' => __('Free', 'wappointment')];
        }

        return apply_filters('wappointment_service_labels', $labels, $this);
    }

    public function locations()
    {
        return $this->belongsToMany('Wappointment\Models\Location', 'wappo_service_location');
    }

    public function isSold()
    {
        return !empty($this->options['woo_sellable']);
    }

    public function hasDuration($duration)
    {
        foreach ($this->options['durations'] as $duration_row) {
            if ($duration_row['duration'] == $duration) {
                return $duration;
            }
        }
        throw new \WappointmentException("Error with duration", 1);
    }

    public function getDurationPriceId($duration)
    {
        foreach ($this->options['durations'] as $duration_row) {
            if ($duration_row['duration'] == $duration && !empty($duration_row['price_id'])) {
                return $duration_row['price_id'];
            }
        }
    }
}
