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
        return $qry->paginate(10);
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
