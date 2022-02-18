<?php

namespace Wappointment\Jobs;

use Wappointment\Models\Appointment;
use Wappointment\Services\JobHelper;
use Wappointment\Services\Wappointment\DotCom;

class ProcessTransaction implements JobInterface
{
    private $appointment;
    private $api;
    private $event;

    public function __construct($params)
    {
        $this->parseParams($params);
        $this->api = new DotCom;
        $this->api->setStaff($this->appointment->staff_id);
    }

    public function handle()
    {
        if ($this->api->isConnected()) {
            switch ($this->event) {
                case 'create':
                    $this->createSafe();
                    break;
                case 'update':
                    $this->updateSafe();
                    break;
                case 'cancel':
                    $this->deleteSafe();
                    break;
                default:
            }
        }
    }

    public function createSafe()
    {
        if ($this->appointment->canSendToDotCom()) {
            $this->api->create($this->appointment);
            $this->appointment->sentToDotCom();
        }
    }
    public function updateSafe()
    {
        if ($this->appointment->canSendToDotCom()) {
            $this->api->create($this->appointment);
            $this->appointment->sentToDotCom();
        } else {
            $this->api->update($this->appointment);
        }
    }

    public function deleteSafe()
    {
        if (!$this->appointment->canSendToDotCom()) {
            $this->api->delete($this->appointment);
        }
    }

    private function parseParams($params)
    {
        $this->event = $params['event'];
        $this->setAppointment($params['appointment']);
    }

    private function setAppointment($appointmentArray)
    {
        if ($this->event == 'cancel') {
            if (!empty($appointmentArray['options'])) {
                $appointmentArray['options'] = json_encode($appointmentArray['options']);
            }
            $this->appointment = (new Appointment)->newFromBuilder($appointmentArray);
        } else {
            $this->appointment = Appointment::find($appointmentArray['id']);
        }
    }
}
