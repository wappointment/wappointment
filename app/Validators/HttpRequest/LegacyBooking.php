<?php

namespace Wappointment\Validators\HttpRequest;

use Wappointment\Validators\Phone;

class LegacyBooking extends AbstractProcessor
{
    protected function validationMessages()
    {
        return [
            'is_phone' => __('Your phone number is not valid', 'wappointment'),
            'email' => __('Your email is not valid', 'wappointment'),
            'skype:regex' => __('Your skype username is not valid', 'wappointment'),
            'time' => __('The selected time is not valid', 'wappointment'),
        ];
    }

    protected function validationRules()
    {
        return [
            'name' => 'required|is_string|max:100',
            'email' => 'required|email',
            'type' => 'required|in:physical,phone,skype,zoom',
            'phone' => 'required_if:type,phone|is_phone',
            'skype' => 'required_if:type,skype|regex:/^[a-zA-Z][a-zA-Z0-9.\-_]{5,31}$/',
            'time' => 'required|min:' . $this->getTimeMin(),
            'ctz' => ''
        ];
    }
    protected function getTimeMin()
    {
        return time() + (\Wappointment\Services\Settings::get('hours_before_booking_allowed') * 60 * 60);
    }

    protected function addValidators()
    {
        $service = \Wappointment\Services\Service::getObject();
        $this->validator->addValidator('is_phone', new Phone($service->getCountries()));
    }

    public function prepareInputs($inputs): array
    {

        if ($inputs['type'] != 'skype') {
            unset($inputs['skype']);
        }

        $inputs['time'] = (int) $inputs['time'];
        return $inputs;
    }
}
