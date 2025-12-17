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
            'time' => __('The selected time is not valid', 'wappointment'),
        ];
    }

    protected function validationRules()
    {
        return [
            'name' => 'required|is_string|max:100',
            'email' => 'required|email',
            'type' => 'required|in:physical,phone,zoom',
            'phone' => 'required_if:type,phone|is_phone',
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


        $inputs['time'] = (int) $inputs['time'];
        return $inputs;
    }
}
