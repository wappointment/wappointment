<?php

namespace Wappointment\Services\Wappointment;

use Wappointment\Services\Settings;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Models\Appointment;
use Wappointment\Services\VersionDB;
use Wappointment\Managers\Central;

class DotCom extends API
{
    public $account_key = '';
    public $staff_id = false;
    private $isLegacy = true;

    public function __construct()
    {
        $this->isLegacy = !VersionDB::atLeast(VersionDB::CAN_CREATE_SERVICES);
        parent::__construct();
    }

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
        if (!empty($this->site_key) && WPHelpers::getOption('appointments_must_refresh')) {
            // 1 - retrieve appointments data
            $appointments = $this->getAppointments();
            if (empty($appointments)) {
                $this->clearMustRefresh();
                return false;
            }

            $appointments_update = WPHelpers::getOption('appointments_update');
            // 2 - check if there are changes since last check

            if ($this->hasPendingChanges($appointments, $appointments_update)) {
                // 3 - loop through each appointment and prepare for update if needs be
                $appointment_collect = \WappointmentLv::collect($appointments_update);
                $requires_update = [];

                foreach ($appointments as $newAppointment) {
                    $found_appointment = $appointment_collect->firstWhere('appointment_id', $newAppointment->appointment_id);

                    if (empty($found_appointment) ||  (int) $newAppointment->updated_at > (int)$found_appointment->updated_at) {
                        $requires_update[$newAppointment->appointment_id] = ['providers' => $newAppointment->options->providers];
                    }
                }


                if (!empty($requires_update)) {
                    $retrieved_appointments = Appointment::select('id', 'options')
                        ->whereIn('id', array_keys($requires_update))->get();

                    foreach ($retrieved_appointments as $updatingAppointment) {
                        $options = empty($updatingAppointment->options) ? [] : $updatingAppointment->options;
                        $merging_options = isset($requires_update[$updatingAppointment->id]) ? $requires_update[$updatingAppointment->id] : [];
                        $updatingAppointment->options = array_merge($options, $merging_options);
                        $updatingAppointment->save();
                    }
                }
                WPHelpers::setOption('appointments_update', $appointments);
            }
        }
        $this->clearMustRefresh();
    }

    public function clearMustRefresh()
    {
        WPHelpers::setOption('appointments_must_refresh', false);
    }

    public function setMustRefresh()
    {
        WPHelpers::setOption('appointments_must_refresh', true);
    }

    public function notifyReset()
    {
        // 0 - only check if site connected
        if (!empty($this->site_key) && $this->isConnected()) {
            $response = $this->client->request(
                'POST',
                $this->call('/api/reseted'),
                [
                    'form_params' => $this->getParams(['account_key' => $this->account_key]),
                    'connect_timeout' => 5
                ]
            );
            return $this->processResponse($response);
        }
    }

    public function hasPendingChanges($appointments, $appointments_update)
    {
        return md5(json_encode($appointments)) !== md5(json_encode($appointments_update));
    }

    public function getAppointments()
    {
        $response = $this->client->request('GET', $this->call('/api/appointment/list/' . $this->site_key), ['connect_timeout' => 5]);
        return $this->processResponse($response);
    }

    public function isConnected()
    {
        return !empty($this->account_key);
    }

    public function connect($account_key = '', $staff_id = false)
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
            $dotcom = $this->confirmAccountKey($result, $account_key, $staff_id);
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
            if ($this->isLegacy) {
                Settings::saveStaff('dotcom', false);
            } else {
                $staff = Central::get('CalendarModel')::findOrFail($this->staff_id);
                $options = $staff->options;
                $options['dotcom'] = false;
                $staff->update([
                    'account_key' => null,
                    'options' => $options
                ]);
            }
        } else {
            throw new \WappointmentException("Failed disconnecting", 1);
        }

        return ['dotcom' => $this->getDotcom(), 'message' => 'Account connected!'];
    }

    public function refresh()
    {
        if (empty($this->account_key)) {
            throw new \WappointmentException("Can't retrieve account key", 1);
        }

        $result = $this->connect($this->account_key);
        $result['message'] = 'Account refreshed';
        return $result;
    }

    public function create($appointment)
    {

        $response = $this->client->request('POST', $this->call('/api/appointment/create'), [
            'form_params' => $this->getParams($this->getAppointmentDetails($appointment))
        ]);

        $result = $this->processResponse($response);
        if ($result) {
            $appointment->sentToDotCom();
            $this->setMustRefresh();
            return ['message' => 'Appointment created!'];
        } else {
            throw new \WappointmentException("Failed creating", 1);
        }
    }

    public function update($appointment)
    {

        $response = $this->client->request('POST', $this->call('/api/appointment/update'), [
            'form_params' => $this->getParams($this->getAppointmentDetails($appointment))
        ]);

        $result = $this->processResponse($response);
        if ($result) {
            $this->setMustRefresh();
            return ['message' => 'Appointment updated!'];
        } else {
            throw new \WappointmentException("Failed updating", 1);
        }
    }

    public function delete($appointment)
    {
        $response = $this->client->request('POST', $this->call('/api/appointment/delete'), [
            'form_params' => $this->getParams([
                'appointment_id' => $appointment->id,
                'account_key' => $this->account_key
            ])
        ]);

        $result = $this->processResponse($response);
        if ($result) {
            $this->setMustRefresh();
            return ['message' => 'Appointment deleted!'];
        } else {
            throw new \WappointmentException("Failed deleting", 1);
        }
    }

    protected function getAppointmentDetails($appointment)
    {
        $tz = Settings::getStaff('timezone', $this->staff_id);
        return [
            'appointment' => $appointment->toDotcom($tz),
            'account_key' => $this->account_key
        ];
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
        if ($this->isLegacy) {
            $this->account_key = $this->getAccountKey();
        } else {
            $staff = Central::get('CalendarModel')::findOrFail($this->staff_id);
            $this->account_key = $staff->account_key;
        }
    }

    protected function confirmAccountKey($result, $account_key, $staff_id = false)
    {
        if (empty($this->getSiteKey())) {
            WPHelpers::setOption('site_key', $result->sitekey);
        }
        $data = [
            'services' => $result->social,
            'account_key' => $account_key
        ];

        if ($this->isLegacy) {
            Settings::saveStaff('dotcom', $data);
        } else {
            $account = $this->isAccountKeyUsed($account_key);
            if ($account) {
                throw new \WappointmentException("Account key already used on another calendar : " . $account->name, 1);
            }
            $staff = Central::get('CalendarModel')::findOrFail($staff_id);

            $options = $staff->options;
            $options['dotcom'] = $data;
            $staff->update([
                'account_key' => $account_key,
                'options' => $options
            ]);
        }

        return $data;
    }

    public function isAccountKeyUsed($account_key)
    {
        return Central::get('CalendarModel')::where('account_key', $account_key)->first();
    }
}
