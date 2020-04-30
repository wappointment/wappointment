<?php

namespace Wappointment\Jobs;

trait IsEmailableJob
{
    public function setTransport()
    {
        return $this->transport = new \Wappointment\Services\Mail();
    }
}
