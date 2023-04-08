<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;
use Wappointment\ClassConnect\SoftDeletes;

class Price extends Model
{
    use SoftDeletes;

    public const TYPE_SERVICE = 0;
    public const TYPE_PACKAGE = 1;

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

    public function generateItemName(TicketAbstract $ticket)
    {
        if ($ticket->boughtWithPackage()) {
            return $this->generatePackageItemName($ticket);
        } else {
            return $ticket->getAppointment()->getServiceName() . ' - ' . $ticket->getAppointment()->getDuration();
        }
    }

    protected function generatePackageItemName(TicketAbstract $ticket)
    {
        $reference = $this->getReference();
        $package_price_id = $ticket->packageVariation();

        foreach ($reference->options['variations'] as $variation) {
            if ($variation['price_id'] == $package_price_id) {
                return $reference->options['name'] . ' - ' . $variation['credits'] . ' ' . $reference->type_label;
            }
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
