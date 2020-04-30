<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Availability;

class AvailabilityRegenerate implements JobInterface
{
    private $staff_id = false;

    public function __construct($params)
    {
        $this->staff_id = $params['staff_id'];
    }

    public function handle()
    {
        return (new Availability())->regenerate($this->staff_id);
    }
}
