<?php

namespace Wappointment\Validators\HttpRequest;

use Wappointment\Validators\Phone;

class Booking extends AbstractProcessor
{
    protected function validationMessages()
    {
        return [
            'is_phone' => 'Your phone number is not valid',
            'email' => 'Your email is not valid',
            'skype:regex' => 'Your skype username is not valid',
            'time' => 'The selected time is not valid',
        ];
    }

    protected function validationRules()
    {
        return [
            'name' => 'required|max:100',
            'email' => 'required|email',
            'type' => 'required|in:physical,phone,skype',
            'phone' => 'required_if:type,phone|is_phone',
            'skype' => 'required_if:type,skype|regex:/^[a-zA-Z][a-zA-Z0-9.\-_]{5,31}$/',
            'time' => 'required|min:' . $this->getTimeMin(),
            'ctz' => ''
        ];
    }
    private function getTimeMin()
    {
        return time() + (\Wappointment\Services\Settings::get('hours_before_booking_allowed') * 60 * 60);
    }

    protected function addValidators()
    {
        $service = \Wappointment\Services\Service::get();
        $this->validator->addValidator('is_phone', new Phone($service->getCountries()));
    }

    public function prepareInputs($inputs): array
    {
        if ($inputs['type'] != 'phone') {
            unset($inputs['phone']);
        }
        if ($inputs['type'] != 'skype') {
            unset($inputs['skype']);
        }

        $inputs['time'] = (int) $inputs['time'];
        return $inputs;
    }
}
