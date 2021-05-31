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

    public function scopeIsService($query)
    {
        return $query->where('type', static::TYPE_SERVICE);
    }

    public function scopeIsPackage($query)
    {
        return $query->where('type', static::TYPE_PACKAGE);
    }

    public function reference()
    {
        return (int)$this->type === static::TYPE_SERVICE ? $this->service() : $this->package();
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
