<?php

namespace Wappointment\Repositories;

trait MustRefreshAvailability
{
    public function refreshAvailability()
    {
        (new Availability)->refresh();
    }
}
