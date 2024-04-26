<?php

namespace Wappointment\Jobs;

use Wappointment\Models\Client;
use Wappointment\Models\Appointment;
use Wappointment\Models\Order;

trait IsAppointmentJob
{
    protected $client = null;
    protected $appointment = null;
    protected $order = null;
    protected $reminder_id = false;

    protected function parseParams($params)
    {

        if (empty($params['client']) || empty($params['appointment'])) {
            throw new \WappointmentException('Missing parameters for the appointment job', 1);
        }

        $this->client = (new Client)->fill($params['client']);
        if (!empty($params['client']['id'])) {
            $this->client->id = $params['client']['id'];
        }

        if (!empty($params['appointment']['options'])) {
            $params['appointment']['options'] = wp_json_encode($params['appointment']['options']);
        }
        $this->appointment = apply_filters('wappointment_appointment_job_params_parse', (new Appointment)->newFromBuilder($params['appointment']), $params);

        if (!empty($params['order'])) {
            $this->order = $params['order'];
        }

        try {
            $this->appointment->refresh();
        } catch (\Throwable $th) {
            //throw $th;
        }

        if (!empty($params['reminder_id'])) {
            $this->reminder_id = $params['reminder_id'];
        }
        $this->params = $params;
    }
}
