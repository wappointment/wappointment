<?php

namespace Wappointment\Validators\HttpRequest;

use Wappointment\Services\CustomFields;

class BookingAdmin extends Booking
{

    public static $startKey = 'start';

    public function generateValidation($inputs)
    {
        $this->validationRulesArray = [
            'email' => 'present|email',
            'start' => 'required|min:' . time(),
            'end' => 'required|min:' . time(),
            'ctz' => '',
            'location' => 'required|min:1',
            'service' => 'required|min:1',
            'duration' => 'required|min:5',
            'clientid' => ''
        ];

        if (empty($inputs['clientid'])) {
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
    }
}
