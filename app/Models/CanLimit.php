<?php

namespace Wappointment\Models;

trait CanLimit
{
    protected $limited = 2;

    public function scopeFetch($qry)
    {
        return $qry->take($this->limited)->get();
    }

    public function scopeFetchPagination($qry)
    {
        return $this->limited > time() - 10 ?  $qry->paginate(10) : $this->scopeFetch($qry);
    }

    public function scopeCanCreate($qry)
    {
        return $qry->count() < $this->limited;
    }

    public function scopeMaxRows()
    {
        return $this->limited;
    }
}
