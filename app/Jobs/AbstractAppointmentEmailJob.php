<?php

namespace Wappointment\Jobs;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;

abstract class AbstractAppointmentEmailJob extends AbstractEmailJob
{
    protected $client = null;
    protected $appointment = null;
    protected $reminder_id = false;
    protected $params = null;

    protected function parseParams($params)
    {
        if (empty($params['client']) || empty($params['appointment'])) {
            throw new \WappointmentException('Missing parameters for the email job', 1);
        }

        $this->client = (new Client)->fill($params['client']);
        if (!empty($params['appointment']['options'])) {
            $params['appointment']['options'] = json_encode($params['appointment']['options']);
        }
        $this->appointment = (new Appointment)->newFromBuilder($params['appointment']);

        if (!empty($params['reminder_id'])) {
            $this->reminder_id = $params['reminder_id'];
        }
        $this->params = $params;
    }

    protected function generateEmail()
    {
        $emailClass = static::EMAIL;
        if ($this->reminder_id) {
            return new $emailClass($this->client, $this->appointment, $this->reminder_id);
        }
        return new $emailClass($this->client, $this->appointment);
    }
}
