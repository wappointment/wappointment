<?php

namespace Wappointment\Models;

use Wappointment\ClassConnect\Model;

abstract class TicketAbstract extends Model
{

    protected $services = [];
    private $stored_appointment = null;
    public $is_participant = false;

    public function hydrateService($services)
    {
        $this->services = !is_array($services) ? [$services] : $services;
    }

    public function getAppointment()
    {
        return $this->stored_appointment;
    }

    public function setAppointment($appointment)
    {
        $this->stored_appointment = $appointment;
    }

    public function boughtWithPackage()
    {
        return !empty($this->options['buying_package']);
    }

    public function packageVariation()
    {
        return $this->options['package_price_id'];
    }

    public function getDurationTicket()
    {
        return is_null($this->stored_appointment) ? $this->getDurationInSec() : $this->stored_appointment->getDurationInSec();
    }

    public function getDurationsPriceIds()
    {
        $ids = [];
        foreach ($this->services as $service) {
            $ids[] = $service->getDurationPriceId($this->getDurationTicket() / 60);
        }
        return $ids;
    }

    public function getServicesPrices()
    {
        return $this->filterPrices($this->queryPrices(
            Price::isService(),
            $this->getDurationsPriceIds()
        ));
    }

    public function paidWithPackage()
    {
        return !empty($this->options['package_price_id']);
    }

    public function getPackagePricesId()
    {
        return $this->paidWithPackage() ? [$this->options['package_price_id']] : false;
    }

    public function getPackagePrices()
    {
        return $this->filterPrices($this->queryPrices(
            Price::isPackage(),
            $this->getPackagePricesId()
        ));
    }

    public function queryPrices($query, $price_ids, $staff_id = false)
    {
        $query->where(function ($query) use ($price_ids) {
            $query->whereIn('parent', $price_ids);
            $query->orWhereIn('id', $price_ids);
        });
        $staff_id = $staff_id === false ? $this->staff_id : $staff_id;
        return $query->where(function ($query) use ($staff_id) {
            $query->whereNull('staff_id');
            $query->orWhere('staff_id', $staff_id);
        })->get();
    }

    public function filterPrices($prices)
    {
        //remove overriden prices
        $getOverridedIds = $prices->filter(function ($e) {
            return !empty($e->staff_id);
        })->map(function ($e) {
            return $e->parent;
        });
        $overridedIds = array_values($getOverridedIds->toArray());
        return $prices->filter(function ($e) use ($overridedIds) {
            return !in_array($e->id, $overridedIds);
        });
    }

    public function recordOrderReference(Order $order)
    {
        $options = $this->options;
        $options['order_id'] = $order->id;
        $this->options = $options;
        $this->save();
    }
}
