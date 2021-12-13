<?php

namespace Wappointment\Jobs;

use Wappointment\Models\Appointment;
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
                    $this->api->create($this->appointment);
                    break;
                case 'update':
                    $this->api->update($this->appointment);
                    break;
                case 'cancel':
                    $this->api->delete($this->appointment);
                    break;
                default:
            }
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
