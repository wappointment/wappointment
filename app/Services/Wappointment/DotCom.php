<?php

namespace Wappointment\Services\Wappointment;

use Wappointment\Services\Settings;
use Wappointment\WP\Helpers as WPHelpers;
use Wappointment\Models\Appointment;
use Wappointment\Services\VersionDB;
use Wappointment\Services\Flag;
use Wappointment\Managers\Central;

class DotCom extends API
{
    public $account_key = '';
    public $staff_id = false;
    private $isLegacy = true;
    private $staff = null;

    public function __construct()
    {
        $this->isLegacy = !VersionDB::atLeast(VersionDB::CAN_CREATE_SERVICES);
        parent::__construct();
    }

    public function setStaff($staff_id = false)
    {
        if ($staff_id !== false) {
            $this->staff_id = !empty($staff_id) ? (int)$staff_id : false;
            if (!$this->isLegacy) {
                $this->staff = Central::get('CalendarModel')::findOrFail($this->staff_id);
            }
            $this->setAccountKey();
        }
    }

    public function checkForUpdates()
    {

        $must_refresh = WPHelpers::getOption('appointments_must_refresh');
        // 0 - only check if site connected
        if (!empty($this->site_key) && (bool)$must_refresh === true && $must_refresh < time()) {
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
            $this->clearMustRefresh();
        }
    }

    public function clearMustRefresh()
    {
        WPHelpers::setOption('appointments_must_refresh', false);
    }

    public function setMustRefresh()
    {
        WPHelpers::setOption('appointments_must_refresh', time() + 60);
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

        try {
            $response = $this->client->request('GET', $this->call('/api/appointment/list/' . $this->site_key), ['connect_timeout' => 5]);
        } catch (\Throwable $th) {
            \Wappointment\Models\Log::data([
                'info' => "Cannot connect to wappointment.com ",
            ]);
            return [];
        }

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
            if ($this->isLegacy) {
                Settings::saveStaff('dotcom', false);
            } else {
                $options = $this->staff->options;
                $options['dotcom'] = false;
                $this->staff->update([
                    'account_key' => null,
                    'options' => $options
                ]);
            }
        } else {
            throw new \WappointmentException("Failed disconnecting", 1);
        }

        return ['message' => 'Account connected!'];
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
        // echo '<pre>';
        // dd($this->getParams($this->getAppointmentDetails($appointment)));
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

        $tz = $this->isLegacy ? Settings::getStaff('timezone', $this->staff_id) : $this->staff->getTimezone();

        return [
            'appointment' => $this->wrapAppointment($appointment->toDotcom($tz)),
            'account_key' => $this->account_key
        ];
    }

    public function wrapAppointment($appointment)
    {
        if ((new Licences)->hasLicenceInstalled() && has_filter('wappointment_ics_signature')) {
            $appointment['no_sign'] = true;
        }
        return $appointment;
    }

    private function getAccountKeyLegacy()
    {
        $dotcom = Settings::getStaff('dotcom', $this->staff_id);
        return empty($dotcom) || empty($dotcom['account_key']) ? '' : $dotcom['account_key'];
    }

    private function setAccountKey()
    {
        $this->account_key = $this->isLegacy ? $this->getAccountKeyLegacy() : $this->staff->account_key;
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

        if ($this->isLegacy) {
            Settings::saveStaff('dotcom', $data);
        } else {
            $account = $this->isAccountKeyUsed($account_key);
            if ($account) {
                throw new \WappointmentException("Account key already used on another calendar : " . $account->name, 1);
            }

            $options = $this->staff->options;
            Flag::save('dotcomSet', true);
            $options['dotcom'] = $data;
            $this->staff->update([
                'account_key' => $account_key,
                'options' => $options
            ]);
        }

        return $data;
    }

    public function isAccountKeyUsed($account_key)
    {
        return Central::get('CalendarModel')::where('account_key', $account_key)->where('id', '!=', $this->staff_id)->first();
    }
}
