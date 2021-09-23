<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\ClassConnect\SoftDeletes;
use Wappointment\Managers\Central;

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
    public function isServiceItem()
    {
        return (int)$this->type === static::TYPE_SERVICE;
    }

    public function isPackageItem()
    {
        return !$this->isServiceItem();
    }

    public function getReference()
    {
        return $this->isServiceItem() ? $this->getService() : $this->getPackage();
    }

    public function generateItemName(Appointment $appointment)
    {
        if ($appointment->boughtWithPackage()) {
            $reference = $this->getReference();
            $package_price_id = $appointment->packageVariation();

            foreach ($reference->options['variations'] as $variation) {
                if ($variation['price_id'] == $package_price_id) {
                    return $reference->options['name'] . ' - ' . $variation['credits'] . ' ' . $reference->type_label;
                }
            }
        } else {
            return $appointment->getServiceName() . ' - ' . $appointment->getDuration();
        }
    }

    public function getPackage()
    {
        return \WappointmentAddonPackages\Models\Package::find($this->reference_id);
    }
    public function getService()
    {
        return Service::find($this->reference_id);
    }
}
