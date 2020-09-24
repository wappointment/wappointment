<?php

namespace Wappointment\Validators\HttpRequest;

use Wappointment\Validators\Phone;

class Client extends AbstractProcessor
{
    protected function validationMessages()
    {
        return [
            'is_phone' => 'Your phone number is not valid',
            'email' => 'Your email is not valid',
            'skype:regex' => 'Your skype username is not valid',
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
