<?php

namespace Wappointment\Validators\HttpRequest;

use Wappointment\Validators\Phone;
use Wappointment\Models\Service as ServiceModel;
use Wappointment\Models\Location as LocationModel;
use Wappointment\Services\CustomFields;
use Wappointment\Services\Settings as SettingsCore;
use Wappointment\ClassConnect\Carbon;

class Booking extends LegacyBooking
{
    protected $service = null;
    public $location = null;
    protected $validationRulesArray = [];
    public static $startKey = 'time';
    protected function validationMessages()
    {
        return [
            'is_phone' => 'Your phone number is not valid',
            'email' => 'Your email is not valid',
            'skype:regex' => 'Your skype username is not valid',
            static::$startKey => 'The selected time is not valid',
        ];
    }

    public function generateValidation($inputs)
    {
        $this->validationRulesArray = [
            'email' => 'required|email',
            static::$startKey => 'required|min:' . $this->getTimeMin(),
            'ctz' => '',
            'location' => 'required|min:1',
            'service' => 'required|min:1',
            'duration' => 'required|min:5'
        ];

        $custom_fields = CustomFields::get();
        foreach ($this->service->options['fields'] as $key => $field) {
            foreach ($custom_fields as $key => $cfield) {
                if ($cfield['namekey'] == $field && empty($this->validationRulesArray[$field])) {
                    $this->validationRulesArray[$field] = !empty($cfield['validations']) ? $cfield['validations'] : '';
                    if ($this->validationRulesArray[$field] == '') {
                        $this->validationRulesArray[$field]  = !empty($cfield['required']) ? 'required' : '';
                    }
                }
            }
        }
        foreach ($this->location->options['fields'] as $key => $field) {
            foreach ($custom_fields as $key => $cfield) {
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
        $service = ServiceModel::find((int)$inputs['service']);
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

    public function validateConditions($inputs)
    {
        $unix_start = $inputs[static::$startKey];
        $unix_end = $inputs[static::$startKey] + $inputs['duration'];

        //check if there are conditions for that appointment
        if (!$this->locationServiceBookableAt($unix_start, $unix_end)) {
            throw new \WappointmentException("Cannot book that service(" . $this->service->name . " ) or location(" . $this->location->name . " ) at that time", 1);
        }
    }
    public function testPassLocation($condition)
    {
        if (!empty($condition['locations'])) {
            return in_array($this->location->id, $condition['locations']);
        }
        return true;
    }
    public function testPassService($condition)
    {
        if (!empty($condition['services'])) {
            return in_array($this->service->id, $condition['services']);
        }
        return true;
    }
    public function locationServiceBookableAt($unix_start, $unix_end)
    {
        $conditions = $this->getConditionsAt($unix_start, $unix_end);
        if (!empty($conditions)) {
            //check if there is a location conditions
            foreach ($conditions as $key => $condition) {
                //test location and service
                if ($this->testPassLocation($condition) && $this->testPassService($condition)) {
                    return true; //if both pass return true
                }
            }
            return false; //there are conditions, but none is satisfying both our parameters
        }
        return true; // if there are no conditions then we pass
    }


    public function getConditionsAt($unix_start, $unix_end)
    {
        static $conditionsOut = false;
        if ($conditionsOut !== false) {
            return $conditionsOut;
        }

        //convert time to staff's time
        $tz = SettingsCore::getStaff('timezone');
        $cStart = Carbon::createFromTimestamp($unix_start)->setTimezone($tz);
        $cEnd = Carbon::createFromTimestamp($unix_end)->setTimezone($tz);

        //get conditions at that time
        //from regav and from punctual time
        return array_merge($this->getRegavConditions($cStart, $cEnd), $this->getPunctualConditions($unix_start, $unix_end));
    }

    public function getPunctualConditions($unix_start, $unix_end)
    {
        $carbon_start = Carbon::createFromTimestamp($unix_start);
        $carbon_end = Carbon::createFromTimestamp($unix_end);

        $conditions = \WappointmentAddonServices\Services\Conditions::get('punctual');

        $conditionsOut = [];
        foreach ($conditions as $key => $condition) {
            if (!empty($condition['conditions'])) {
                if ($condition['start'] < $carbon_start->timestamp && $condition['end'] > $carbon_start->timestamp) {
                    //part or entire slot is covered by this, in which case we return the conditions
                    $conditionsOut[] = $condition['conditions'];
                } else {
                    if ($condition['end'] > $carbon_end->timestamp && $condition['start'] < $carbon_end->timestamp) {
                        //part or entire slot is covered by this, in which case we return the conditions
                        $conditionsOut[] = $condition['conditions'];
                    }
                }
            }
        }

        return $conditionsOut;
    }

    public function getRegavConditions($cStart, $cEnd)
    {
        $conditions = \WappointmentAddonServices\Services\Conditions::get('regular');

        $dayKeys = [$this->getDayKey($cStart)];
        if ($cStart->dayOfWeekIso != $cEnd->dayOfWeekIso) {
            $dayKeys[] = [$this->getDayKey($cEnd)];
        }

        $conditionsOut = [];
        foreach ($dayKeys as $i => $dayKey) {
            //dd('condi test', $dayKey, $conditions[$dayKey]);
            if (!empty($conditions[$dayKey])) {
                foreach ($conditions[$dayKey] as $key => $hours) {
                    if ($i == 0) { // first day
                        if ($hours[0] <= $cStart->hour && $hours[1] >= $cStart->hour) {
                            //part or entire slot is covered by this, in which case we return the conditions
                            $conditionsOut[] = $hours[2];
                        }
                    } else { // second day
                        if ($hours[1] >= $cEnd->hour && $hours[0] <= $cEnd->hour) {
                            //part or entire slot is covered by this, in which case we return the conditions
                            $conditionsOut[] = $hours[2];
                        }
                    }
                }
            }
        }

        return $conditionsOut;
    }

    public function getDayKey($carbonDate)
    {
        switch ($carbonDate->dayOfWeekIso) {
            case 1:
                return 'monday';
            case 2:
                return 'tuesday';
            case 3:
                return 'wednesday';
            case 4:
                return 'thursday';
            case 5:
                return 'friday';
            case 6:
                return 'saturday';
            case 7:
                return 'sunday';
        }
    }


    public function prepareInputs($inputs): array
    {
        $this->validateService($inputs);
        $this->validateLocation($inputs);
        //$this->validateConditions($inputs);
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
        $custom_fields = $this->getFields();
        $dataClient = ['options' => []];
        foreach ($custom_fields as $key => $cfield) {
            if (in_array($cfield, ['location', 'duration', 'service', 'time', 'start', 'clientid', 'end'])) {
                continue;
            }
            switch ($cfield) {
                case 'email':
                    $dataClient['email'] = $this->get('email');
                    break;
                case 'name':
                    $dataClient['name'] = $this->get('name');
                    break;
                case 'ctz':
                    $dataClient['options']['tz'] = $this->get('ctz');
                    break;

                default:
                    $dataClient['options'][$cfield] = $this->get($cfield);
                    break;
            }
        }
        return $dataClient;
    }
}
