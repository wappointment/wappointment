<?php

namespace Wappointment\Validators\HttpRequest;

use Wappointment\Validators\Phone;
use Wappointment\Managers\Service as ServiceCentral;
use Wappointment\Models\Location as LocationModel;
use Wappointment\Managers\Central;
use Wappointment\Models\Calendar;
use Wappointment\Services\Settings;
use Wappointment\Services\VersionDB;
use Wappointment\WP\Helpers;

class Booking extends LegacyBooking
{
    protected $service = null;
    public $location = null;
    public $staff = null;
    protected $validationRulesArray = [];
    public static $startKey = 'time';

    protected function validationMessages()
    {
        return [
            'is_phone' => __('Your phone number is not valid', 'wappointment'),
            'email' => __('Your email is not valid', 'wappointment'),
            'skype:regex' => __('Your skype username is not valid', 'wappointment'),
            static::$startKey => __('The selected time is not valid', 'wappointment'),
        ];
    }
    public function getUserEmail()
    {
        return $this->forceEmail() && $this->isLogged() ? Helpers::currentUserEmail() : $this->get('email');
    }

    protected function forceEmail()
    {
        return Settings::get('forceemail');
    }

    public function isLogged()
    {
        return Helpers::auth();
    }

    public function generateValidation($inputs)
    {
        $this->validationRulesArray = [
            static::$startKey => 'required|min:' . $this->getTimeMin(),
            'ctz' => '',
            'location' => 'required|min:1',
            'service' => 'required|min:1',
            'duration' => 'required|min:5',
            'staff_id' => '',
            'package_id' => '',
            'package_price_id' => '',
            'slots' => '',
            'appointment_key' => ''
        ];

        if (!$this->forceEmail() || !$this->isLogged()) {
            $this->validationRulesArray['email'] = 'required|email';
        }

        $custom_fields = Central::get('CustomFields')::get();
        $this->addCustomValidations($this->service, $custom_fields);
        $this->addCustomValidations($this->location, $custom_fields);

        $this->validationRulesArray = apply_filters('wappointment_booking_validation_rules', $this->validationRulesArray, $this->service);
    }

    protected function addCustomValidations($model, $custom_fields)
    {
        foreach ($model->options['fields'] as $field) {
            foreach ($custom_fields as $cfield) {
                if ($cfield['namekey'] == $field && empty($this->validationRulesArray[$field])) {
                    $this->validationRulesArray[$field] = !empty($cfield['validations']) ? $cfield['validations'] : '';
                    if ($this->validationRulesArray[$field] == '') {
                        $this->validationRulesArray[$field]  = !empty($cfield['required']) ? 'required' : '';
                    }
                }
            }
        }
    }

    public function getFields()
    {
        return array_keys($this->validationRulesArray);
    }

    /**
     * Ignore certain given values
     *
     * @return void
     */
    public function getFieldsFiltered()
    {
        return array_filter($this->getFields(), function ($var) {
            return !in_array($var, ['recurrent', 'page']);
        });
    }

    protected function validationRules()
    {
        return $this->validationRulesArray;
    }

    protected function addValidators()
    {

        $location = LocationModel::find((int)$this->request->input('location'));

        if (LocationModel::TYPE_PHONE === $location->type) {
            $countries = empty($location->options['countries']) ? [] : $location->options['countries'];
        } else {
            $countries = empty($this->service->options['countries']) ? [] : $this->service->options['countries'];
        }

        $this->validator->addValidator('is_phone', new Phone($countries));
    }

    public function validateService($inputs)
    {
        if (!is_numeric($inputs['service'])) {
            throw new \WappointmentException("Service data error", 1);
        }
        $service = ServiceCentral::model()::find((int)$inputs['service']);
        if (empty($service)) {
            throw new \WappointmentException("Service data error 2", 1);
        }
        $this->service = $service;
    }

    public function validateLocation($inputs)
    {
        if (!is_numeric($inputs['location'])) {
            throw new \WappointmentException("Delivery Modality data error", 1);
        }
        if (count($this->service->locations) == 0) {
            throw new \WappointmentException("Delivery Modality data error", 1);
        }
        foreach ($this->service->locations as $key => $location) {
            if ($location->id == (int) $inputs['location']) {
                $this->location = (object) $location->toArray();

                if (empty($this->location->options['fields'])) {
                    $this->location->options['fields'] = [];
                }
                if ($this->location->type == 2) {
                    $this->location->options['fields'][] = 'phone';
                }
                if ($this->location->type == 3) {
                    $this->location->options['fields'][] = 'skype';
                }
            }
        }
        if (empty($this->location)) {
            throw new \WappointmentException("Location data error 2", 1);
        }
    }

    public function prepareInputs($inputs): array
    {
        if (VersionDB::canServices()) {
            $this->staff = Calendar::active()->findOrFail((int)$inputs['staff_id']);
        }
        $this->validateService($inputs);
        $this->validateLocation($inputs);

        $result = apply_filters('wappointment_validate_booking', true, $inputs, $this->service, $this->location, static::$startKey, $this->staff);

        if ($result !== true) {
            throw new \WappointmentException("Error Processing Request", 1);
        }

        $this->generateValidation($inputs);
        $inputs[static::$startKey] = (int) $inputs[static::$startKey];
        return $inputs;
    }

    public function getService()
    {
        return $this->service;
    }

    public function preparedData()
    {
        $custom_fields = $this->getFieldsFiltered();
        $dataClient = ['options' => []];
        foreach ($custom_fields as $cfield) {
            if (in_array($cfield, ['location', 'duration', 'service', 'time', 'start', 'clientid', 'end'])) {
                continue;
            }
            switch ($cfield) {
                case 'email':
                    $dataClient['email'] = $this->getUserEmail();
                    break;
                case 'name':
                    $dataClient['name'] = $this->get('name');
                    break;
                case 'ctz':
                    $dataClient['options']['tz'] = $this->get('ctz');
                    break;
                case 'slots':
                case 'package_id':
                case 'package_price_id':
                case 'staff_id':
                    break;
                default:
                    $dataClient['options'][$cfield] = $this->get($cfield);
                    break;
            }
        }

        return $dataClient;
    }
}
