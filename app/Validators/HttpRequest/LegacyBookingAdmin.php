<?php

namespace Wappointment\Validators\HttpRequest;

use Wappointment\Validators\Phone;

class LegacyBookingAdmin extends AbstractProcessor
{
    protected function validationMessages()
    {
        return [
            'is_phone' => __('Your phone number is not valid', 'wappointment'),
            'email' => __('Your email is not valid', 'wappointment'),
        ];
    }

    protected function validationRules()
    {
        if (!empty($this->input('clientid'))) {
            return [
                'name' => 'required_if:type,phone|present|max:100',
                'type' => 'present|in:physical,phone,zoom',
                'start' => 'required|min:' . time(),
                'end' => 'required|min:' . time(),
                'timezone' => '',
                'clientid' => ''
            ];
        } else {
            return [
                'name' => 'required_if:type,phone|present|max:100',
                'email' => 'present|email',
                'type' => 'present|in:physical,phone,zoom',
                'phone' => 'required_if:type,phone|is_phone',
                'start' => 'required|min:' . time(),
                'end' => 'required|min:' . time(),
                'timezone' => '',
                'clientid' => ''
            ];
        }
    }

    protected function addValidators()
    {
        $service = \Wappointment\Services\Service::getObject();
        $this->validator->addValidator('is_phone', new Phone($service->getCountries()));
    }

    public function prepareInputs($inputs): array
    {
        $inputs['start'] = (int) $inputs['start'];
        $inputs['end'] = (int) $inputs['end'];
        return $inputs;
    }
}
