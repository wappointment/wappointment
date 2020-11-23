<?php

namespace Wappointment\Services\Wappointment;

use Wappointment\Services\Settings;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Models\Appointment;

class DotCom extends API
{
    public $account_key = '';
    public $staff_id = false;

    public function setStaff($staff_id = false)
    {
        if ($staff_id !== false) {
            $this->staff_id = !empty($staff_id) ? (int)$staff_id : false;
            $this->setAccountKey();
        }
    }

    public function checkForUpdates()
    {

        // 0 - only check if site connected
        if (!empty($this->site_key)) {
            // 1 - retrieve appointments data
            $appointments = $this->getAppointments();
            $appointments_update = WPHelpers::getOption('appointments_update');
            // 2 - check if there are changes since last check

            if ($this->hasPendingChanges($appointments, $appointments_update)) {
                // 3 - loop through each appointment and prepare for update if needs be
                $appointment_collect = \WappointmentLv::collect($appointments_update);
                $requires_update = [];

                foreach ($appointments as $newAppointment) {
                    $found_appointment = $appointment_collect->firstWhere('appointment_id', $newAppointment->appointment_id);

                    if (empty($found_appointment) || (int)$found_appointment->updated_at > (int) $newAppointment->updated_at) {
                        $requires_update[$newAppointment->appointment_id] = ['providers' => $newAppointment->options->providers];
                    }
                }

                if (!empty($requires_update)) {
                    $retrieved_appointments = Appointment::select('id', 'options')->whereIn('id', array_keys($requires_update))->get();

                    foreach ($retrieved_appointments as $updatingAppointment) {
                        $options = $updatingAppointment->options;
                        $updatingAppointment->options = array_merge($options, $requires_update[$newAppointment->appointment_id]);
                        $updatingAppointment->save();
                    }
                }

                WPHelpers::setOption('appointments_update', $appointments);
            }
        }
    }

    public function hasPendingChanges($appointments, $appointments_update)
    {
        return md5(json_encode($appointments)) !== md5(json_encode($appointments_update));
    }

    public function getAppointments()
    {
        $response = $this->client->request('GET', $this->call('/api/appointment/list/' . $this->site_key));
        return $this->processResponse($response);
    }

    public function isConnected()
    {
        return !empty($this->account_key);
    }

    public function connect($account_key = '')
    {
        if (strlen($account_key) > 48 || strlen($account_key) < 32) {
            throw new \WappointmentException("Account key is invalid", 1);
        }

        $response = $this->client->request('POST', $this->call('/api/connect'), [
            'form_params' => $this->getParams(['account_key' => $account_key])
        ]);

        $result = $this->processResponse($response);

        $dotcom = false;
        if ($result) {
            $dotcom = $this->confirmAccountKey($result, $account_key);
        }

        return ['dotcom' => $dotcom, 'message' => 'Account connected!'];
    }

    public function disconnect()
    {
        if (empty($this->account_key)) {
            throw new \WappointmentException("Can't retrieve account key", 1);
        }

        $response = $this->client->request('POST', $this->call('/api/disconnect'), [
            'form_params' => $this->getParams(['account_key' => $this->account_key])
        ]);

        $result = $this->processResponse($response);
        if ($result) {
            //disconnect
            Settings::saveStaff('dotcom', false);
        } else {
            throw new \WappointmentException("Failed disconnecting", 1);
        }

        return ['dotcom' => $this->getDotcom(), 'message' => 'Account connected!'];
    }

    public function create($appointment)
    {

        $response = $this->client->request('POST', $this->call('/api/appointment/create'), [
            'form_params' => $this->getParams([
                'appointment' => [
                    'title' => $appointment->getTitle(),
                    'starts_at' => $appointment->start_at->timestamp,
                    'appointment_id' => $appointment->id,
                    'duration' => $appointment->getDurationInSec(),
                    'timezone' => Settings::getStaff('timezone', $appointment->staff_id),
                    'emails' => [
                        $appointment->client->email
                    ]
                ],
                'account_key' => $this->account_key
            ])
        ]);

        $result = $this->processResponse($response);
        if ($result) {
        } else {
            throw new \WappointmentException("Failed creating", 1);
        }

        return ['message' => 'Appointment created!'];
    }

    public function update($appointment)
    {

        $response = $this->client->request('POST', $this->call('/api/appointment/update'), [
            'form_params' => $this->getParams([
                'appointment' => $appointment,
                'account_key' => $this->account_key
            ])
        ]);

        $result = $this->processResponse($response);
        if ($result) {
        } else {
            throw new \WappointmentException("Failed updating", 1);
        }

        return ['message' => 'Appointment updated!'];
    }

    public function delete($appointment)
    {

        $response = $this->client->request('POST', $this->call('/api/appointment/delete'), [
            'form_params' => $this->getParams([
                'appointment' => $appointment,
                'account_key' => $this->account_key
            ])
        ]);

        $result = $this->processResponse($response);
        if ($result) {
        } else {
            throw new \WappointmentException("Failed deleting", 1);
        }

        return ['message' => 'Appointment deleted!'];
    }

    private function getDotcom()
    {
        return Settings::getStaff('dotcom', $this->staff_id);
    }

    private function getAccountKey()
    {
        $dotcom = $this->getDotcom();
        return empty($dotcom) || empty($dotcom['account_key']) ? '' : $dotcom['account_key'];
    }

    private function setAccountKey()
    {
        $this->account_key = $this->getAccountKey();
    }

    protected function confirmAccountKey($result, $account_key)
    {
        if (empty($this->getSiteKey())) {
            WPHelpers::setOption('site_key', $result->sitekey);
        }
        $data = [
            'services' => $result->social,
            'account_key' => $account_key
        ];
        Settings::saveStaff('dotcom', $data);
        return $data;
    }
}
