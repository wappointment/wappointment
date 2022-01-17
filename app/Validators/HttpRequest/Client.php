<?php

namespace Wappointment\Validators\HttpRequest;

use Wappointment\Validators\Phone;

class Client extends AbstractProcessor
{
    protected function validationMessages()
    {
        return [
            'is_phone' => __('Your phone number is not valid', 'wappointment'),
            'email' => __('Your email is not valid', 'wappointment'),
            'skype:regex' => __('Your skype username is not valid', 'wappointment'),
        ];
    }

    protected function validationRules()
    {
        return [
            'name' => 'required_if:type,phone|present|max:100',
            'email' => 'present|email',
            'options' => '',
            'options.phone' => 'present|is_phone',
            'options.skype' => 'present|regex:/^[a-zA-Z][a-zA-Z0-9.\-_]{5,31}$/',
        ];
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
