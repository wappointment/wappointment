<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\ClassConnect\SoftDeletes;

class Price extends Model
{
    use SoftDeletes;

    const TYPE_SERVICE = 0;
    const TYPE_PACKAGE = 1;

    protected $dates = ['deleted_at'];
    protected $table = 'wappo_prices';
    protected $visible = ['id', 'reference_id', 'type', 'name', 'price', 'staff_id', 'parent'];
    protected $fillable = ['reference_id', 'type', 'name', 'price', 'staff_id', 'parent'];

    public function scopeService($query)
    {
        return $query->where('type', static::TYPE_SERVICE);
    }

    public function scopePackage($query)
    {
        return $query->where('type', static::TYPE_PACKAGE);
    }
}
