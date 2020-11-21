<?php

namespace Wappointment\Jobs;

use Wappointment\Services\Wappointment\DotCom;

class SendAppointment implements JobInterface
{
    private $appointment = false;
    private $action = '';

    public function __construct($params)
    {
        $this->action = $params['action'];
        $this->appointment = $params['appointment'];
    }

    public function handle()
    {

        $dotcomapi = new DotCom;
        $dotcomapi->setStaff($this->appointment->staff_id);
        switch ($this->action) {
            case 'create':
                return $dotcomapi->create($this->appointment);
            case 'update':
                return $dotcomapi->update($this->appointment);
            case 'delete':
                return $dotcomapi->delete($this->appointment);
        }
    }
}
